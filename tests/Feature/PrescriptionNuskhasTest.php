<?php

namespace Tests\Feature;

use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PrescriptionNuskhasTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private int $clinic;

    private int $patient;

    private int $template;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);

        $this->clinic = DB::table('clinics')->insertGetId([
            'name' => 'Clinic One', 'currency' => 'AED', 'created_at' => now(),
        ], 'clinic_id');
        $role = DB::table('roles')->insertGetId(['name' => 'Owner'], 'role_id');
        $userId = DB::table('users')->insertGetId([
            'clinic_id' => $this->clinic, 'role_id' => $role,
            'name' => 'Clinic Owner', 'email' => 'owner@example.com',
            'password' => bcrypt('password123456'), 'is_active' => true,
            'created_at' => now(),
        ], 'user_id');
        $this->owner = User::findOrFail($userId);
        $this->patient = DB::table('patients')->insertGetId([
            'clinic_id' => $this->clinic, 'full_name' => 'Patient One',
            'patient_code' => 'P-01', 'created_at' => now(),
        ], 'patient_id');
        $this->template = DB::table('nuskha_templates')->insertGetId([
            'clinic_id' => $this->clinic, 'name' => 'Gulab Saunf Blend',
            'instructions' => 'PRIVATE RECIPE NOTES', 'is_active' => true,
        ], 'nuskha_template_id');
        DB::table('nuskha_items')->insert([
            'nuskha_template_id' => $this->template,
            'item_name' => 'PRIVATE INGREDIENT', 'quantity' => 900, 'unit' => 'g',
        ]);
        $this->actingAs($this->owner);
    }

    private function payload(array $nuskhas = []): array
    {
        return [
            'patient_id' => $this->patient,
            'doctor_user_id' => $this->owner->user_id,
            'prescribed_at' => '2026-10-21T10:30',
            'status' => 'issued',
            'general_instructions' => 'Follow the prescribed directions.',
            'nuskhas' => $nuskhas ?: [
                [
                    'source' => 'template', 'nuskha_template_id' => $this->template,
                    'nuskha_name' => 'FORGED TEMPLATE NAME',
                    'quantity' => '20', 'unit' => 'g', 'frequency' => 'Twice daily',
                ],
                [
                    'source' => 'custom', 'nuskha_name' => 'Custom Herbal Blend',
                    'quantity' => '2', 'unit' => 'bottle',
                ],
            ],
        ];
    }

    public function test_multiple_template_and_custom_nuskhas_save_without_copying_ingredients(): void
    {
        $response = $this->post(route('prescriptions.store'), $this->payload());
        $response->assertSessionHasNoErrors();
        $rx = Prescription::firstOrFail();
        $response->assertRedirect(route('prescriptions.show', $rx));
        $this->assertCount(2, $rx->nuskhas);
        $this->assertSame('Gulab Saunf Blend', $rx->nuskhas[0]->nuskha_name);
        $this->assertSame('20.000', $rx->nuskhas[0]->quantity);
        $this->assertSame('bottle', $rx->nuskhas[1]->unit);
        $this->assertDatabaseCount('prescription_items', 0);
        $this->assertDatabaseHas('nuskha_items', ['item_name' => 'PRIVATE INGREDIENT']);

        $this->get(route('prescriptions.show', $rx))->assertOk()
            ->assertSee('Gulab Saunf Blend')->assertSee('Custom Herbal Blend')
            ->assertSee('20 g')->assertSee('2 bottle')
            ->assertDontSee('PRIVATE INGREDIENT')->assertDontSee('PRIVATE RECIPE NOTES');
        $this->get(route('prescriptions.edit', $rx))->assertOk()
            ->assertSee('Add another nuskha')
            ->assertDontSee('PRIVATE INGREDIENT')->assertDontSee('PRIVATE RECIPE NOTES')
            ->assertDontSee('data-rx-template-data', false);
        $this->get(route('prescriptions.index'))->assertOk()->assertSee('2');
    }

    public function test_total_quantity_and_unit_are_required_and_invalid_units_rejected(): void
    {
        $data = $this->payload();
        unset($data['nuskhas'][0]['quantity']);
        $data['nuskhas'][1]['unit'] = 'invalid';
        $this->post(route('prescriptions.store'), $data)
            ->assertSessionHasErrors(['nuskhas.0.quantity', 'nuskhas.1.unit']);
        $this->assertDatabaseCount('prescriptions', 0);
    }

    public function test_foreign_clinic_templates_and_patients_are_rejected(): void
    {
        $other = DB::table('clinics')->insertGetId([
            'name' => 'Clinic Two', 'created_at' => now(),
        ], 'clinic_id');
        $foreignTemplate = DB::table('nuskha_templates')->insertGetId([
            'clinic_id' => $other, 'name' => 'Foreign Nuskha', 'is_active' => true,
        ], 'nuskha_template_id');
        $data = $this->payload();
        $data['nuskhas'][0]['nuskha_template_id'] = $foreignTemplate;
        $this->post(route('prescriptions.store'), $data)
            ->assertSessionHasErrors('nuskhas.0.nuskha_template_id');

        $data = $this->payload();
        $data['patient_id'] = DB::table('patients')->insertGetId([
            'clinic_id' => $other, 'full_name' => 'Foreign Patient',
            'patient_code' => 'P-02', 'created_at' => now(),
        ], 'patient_id');
        $this->post(route('prescriptions.store'), $data)
            ->assertSessionHasErrors('patient_id');
    }

    public function test_edit_removes_adds_and_preserves_original_template_name(): void
    {
        $this->post(route('prescriptions.store'), $this->payload())->assertSessionHasNoErrors();
        $rx = Prescription::firstOrFail();
        $first = $rx->nuskhas->first();
        $removed = $rx->nuskhas->last()->getKey();
        DB::table('nuskha_templates')->where('nuskha_template_id', $this->template)
            ->update(['name' => 'Renamed Library Entry', 'is_active' => false]);

        $data = $this->payload([
            [
                'assignment_id' => $first->getKey(), 'source' => 'template',
                'nuskha_template_id' => $this->template, 'quantity' => '0.5', 'unit' => 'kg',
            ],
            ['source' => 'custom', 'nuskha_name' => 'Replacement', 'quantity' => '100', 'unit' => 'ml'],
        ]);
        $this->put(route('prescriptions.update', $rx), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('prescription_nuskhas', [
            'prescription_nuskha_id' => $first->getKey(),
            'nuskha_name' => 'Gulab Saunf Blend', 'unit' => 'kg',
        ]);
        $this->assertDatabaseMissing('prescription_nuskhas', ['prescription_nuskha_id' => $removed]);
        $this->assertCount(2, $rx->fresh()->nuskhas);

        $new = $this->payload();
        $this->post(route('prescriptions.store'), $new)
            ->assertSessionHasErrors('nuskhas.0.nuskha_template_id');
    }

    public function test_existing_assignment_id_cannot_be_attached_to_another_prescription(): void
    {
        $this->post(route('prescriptions.store'), $this->payload())->assertSessionHasNoErrors();
        $first = Prescription::firstOrFail();
        $data = $this->payload();
        $data['nuskhas'][0]['assignment_id'] = $first->nuskhas->first()->getKey();
        $this->post(route('prescriptions.store'), $data)
            ->assertSessionHasErrors('nuskhas.0.assignment_id');
        $this->assertDatabaseCount('prescriptions', 1);
    }

    public function test_legacy_ingredients_are_never_shown_or_used_as_total_weight(): void
    {
        $rx = Prescription::create([
            'clinic_id' => $this->clinic, 'patient_id' => $this->patient,
            'created_by_user_id' => $this->owner->user_id,
            'doctor_user_id' => $this->owner->user_id,
            'prescription_no' => 'RX-OLD', 'nuskha_name' => 'Historical Nuskha',
            'prescribed_at' => '2026-10-20 10:00:00', 'status' => 'issued',
        ]);
        $rx->items()->create(['item_name' => 'LEGACY SECRET', 'quantity' => 900, 'unit' => 'g']);
        $this->get(route('prescriptions.show', $rx))->assertOk()
            ->assertSee('Historical Nuskha')->assertDontSee('LEGACY SECRET');
        $this->get(route('prescriptions.edit', $rx))->assertOk()
            ->assertSee('no recorded finished-nuskha quantity')
            ->assertDontSee('LEGACY SECRET')->assertDontSee('value="900', false);

        $this->put(route('prescriptions.update', $rx), $this->payload())
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('prescription_items', ['item_name' => 'LEGACY SECRET', 'quantity' => 900]);
        $this->assertCount(2, $rx->fresh()->nuskhas);
    }
}
