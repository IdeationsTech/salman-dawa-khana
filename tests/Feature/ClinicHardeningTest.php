<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\NuskhaOrder;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Services\DatabaseBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClinicHardeningTest extends TestCase
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

    private function asRole(string $name): void
    {
        $role = DB::table('roles')->insertGetId(['name' => $name], 'role_id');
        $this->owner->update(['role_id' => $role]);
        $this->actingAs($this->owner->fresh());
    }

    private function billPayload(): array
    {
        return ['request_key' => (string) Str::uuid(), 'mode' => 'new_bill', 'patient_id' => $this->patient,
            'payment_date' => '2026-10-21T10:00', 'description' => 'Consultation', 'subtotal' => '1000', 'discount' => '0', 'amount' => '0'];
    }

    public function test_receptionist_cannot_access_recipes_write_prescriptions_or_delete_payments(): void
    {
        $this->asRole('Receptionist');
        $this->get(route('prescriptions.templates.edit', $this->template))->assertForbidden();
        $this->get(route('prescriptions.create'))->assertForbidden();
        $this->post(route('prescriptions.store'), [])->assertForbidden();
        $this->get(route('visits.create'))->assertOk();
        $this->get(route('payments.create'))->assertOk()->assertDontSee('PRIVATE INGREDIENT');
        $this->get(route('prescriptions.index'))->assertOk()->assertDontSee('Create prescription');
        $this->delete(route('patients.destroy', $this->patient))->assertForbidden();
    }

    public function test_duplicate_new_bill_and_payment_submissions_are_saved_once(): void
    {
        $bill = $this->billPayload();
        $this->post(route('payments.store'), $bill)->assertSessionHasNoErrors();
        $this->post(route('payments.store'), $bill)->assertSessionHasNoErrors();
        $this->assertSame(1, Bill::count());
        $receipt = ['request_key' => (string) Str::uuid(), 'mode' => 'existing_bill', 'patient_id' => $this->patient,
            'bill_id' => Bill::first()->bill_id, 'payment_date' => '2026-10-21T10:01', 'amount' => '100', 'method' => 'cash'];
        $this->post(route('payments.store'), $receipt)->assertSessionHasNoErrors();
        $this->post(route('payments.store'), $receipt)->assertSessionHasNoErrors();
        $this->assertSame(1, Payment::count());
        $this->assertSame('100.00', Bill::first()->paid_amount);
        $receipt['amount'] = '200';
        $this->post(route('payments.store'), $receipt)->assertStatus(409);
    }

    public function test_validation_failure_can_retry_same_submission_key(): void
    {
        $data = $this->billPayload();
        $data['subtotal'] = 'invalid';
        $this->post(route('payments.store'), $data)->assertSessionHasErrors('subtotal');
        $data['subtotal'] = '1000';
        $this->post(route('payments.store'), $data)->assertSessionHasNoErrors();
        $this->post(route('payments.store'), $data)->assertSessionHasNoErrors();
        $this->assertSame(1, Bill::count());
    }

    public function test_clinic_login_is_limited_after_five_wrong_attempts(): void
    {
        auth()->logout();
        RateLimiter::clear('clinic-login:'.hash('sha256', 'owner@example.com|127.0.0.1'));
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.submit'), ['email' => 'owner@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $response = $this->post(route('login.submit'), ['email' => 'owner@example.com', 'password' => 'password123456']);
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Too many login attempts', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_search_is_bounded_and_forms_only_hydrate_selected_patient(): void
    {
        foreach (range(1, 25) as $i) {
            DB::table('patients')->insert(['clinic_id' => $this->clinic, 'patient_code' => 'A'.$i, 'full_name' => 'Adnan '.$i, 'created_at' => now()]);
        }
        $this->getJson(route('patients.lookup', ['q' => 'ad']))->assertOk()->assertJsonCount(20, 'patients')->assertJsonPath('has_more', true);
        $this->get(route('visits.create'))->assertOk()->assertViewHas('patients', fn ($p) => $p->isEmpty())->assertDontSee('Adnan 25');
        $this->get(route('payments.create'))->assertOk()->assertViewHas('patients', fn ($p) => $p->isEmpty())->assertViewHas('billData', []);
        $this->get(route('payments.create', ['patient_id' => $this->patient]))->assertOk()->assertViewHas('patients', fn ($p) => $p->count() === 1);
        $this->post(route('payments.store'), $this->billPayload())->assertSessionHasNoErrors();
        $this->getJson(route('payments.patient-bills', $this->patient))->assertOk()->assertJsonCount(1, 'bills');
        $other = DB::table('clinics')->insertGetId(['name' => 'Other', 'created_at' => now()], 'clinic_id');
        $foreign = DB::table('patients')->insertGetId(['clinic_id' => $other, 'patient_code' => 'FOREIGN', 'full_name' => 'Adnan Secret', 'created_at' => now()], 'patient_id');
        $this->getJson(route('payments.patient-bills', $foreign))->assertNotFound();
        $this->getJson(route('patients.lookup', ['q' => 'Adnan Secret']))->assertJsonCount(0, 'patients');
    }

    public function test_timezone_round_trip_and_report_midnight_boundary(): void
    {
        Setting::create(['clinic_id' => $this->clinic, 'setting_key' => 'timezone', 'setting_value' => 'Asia/Karachi']);
        $data = $this->billPayload();
        $data['payment_date'] = '2026-10-21T00:30';
        $data['amount'] = '100';
        $data['method'] = 'cash';
        $this->post(route('payments.store'), $data)->assertSessionHasNoErrors();
        $payment = Payment::firstOrFail();
        $this->assertSame('2026-10-20 23:30:00', DB::table('payments')->value('payment_date'));
        $this->assertSame('2026-10-21 00:30', $payment->payment_date->format('Y-m-d H:i'));
        $this->get(route('payments.edit', $payment))->assertOk()->assertSee('2026-10-21T00:30', false);
        $this->get(route('reports.index', ['from' => '2026-10-21', 'to' => '2026-10-21']))->assertOk()->assertViewHas('totalIncome', '100.00');
        $this->get(route('reports.index', ['from' => '2026-10-20', 'to' => '2026-10-20']))->assertOk()->assertViewHas('totalIncome', '0.00');
    }

    public function test_owner_can_change_timezone_and_receptionist_cannot(): void
    {
        $this->put(route('settings.update'), ['settings' => ['timezone' => 'Asia/Karachi']])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('settings', ['clinic_id' => $this->clinic, 'setting_key' => 'timezone', 'setting_value' => 'Asia/Karachi']);
        $this->put(route('settings.update'), ['settings' => ['timezone' => 'invalid']])->assertSessionHasErrors('settings.timezone');
        $this->asRole('Receptionist');
        $this->put(route('settings.update'), ['settings' => ['timezone' => 'UTC']])->assertForbidden();
    }

    public function test_encrypted_backup_verifies_and_restores_database_and_uploads(): void
    {
        $directory = storage_path('framework/testing/backups-'.Str::uuid());
        config(['backups.path' => $directory]);
        $upload = storage_path('app/public/review-backup-fixture/avatar.txt');
        File::ensureDirectoryExists(dirname($upload));
        File::put($upload, 'Original upload');
        try {
            $backups = app(DatabaseBackupService::class);
            $path = $backups->create();
            $this->assertStringNotContainsString('Patient One', File::get($path));
            $this->assertArrayHasKey('review-backup-fixture/avatar.txt', $backups->inspect($path)['public_files']);
            $this->artisan('clinic:restore', ['file' => $path])->assertExitCode(0);
            DB::table('patients')->where('patient_id', $this->patient)->update(['full_name' => 'Changed']);
            File::put($upload, 'Changed upload');
            $backups->restore($path);
            $this->assertDatabaseHas('patients', ['patient_id' => $this->patient, 'full_name' => 'Patient One']);
            $this->assertSame('Original upload', File::get($upload));
            $this->artisan('clinic:restore', ['file' => $path, '--force' => true])->assertExitCode(1);
            File::put($path, 'corrupt backup');
            $this->artisan('clinic:restore', ['file' => $path])->assertExitCode(1);
            $this->assertDatabaseHas('patients', ['patient_id' => $this->patient, 'full_name' => 'Patient One']);
        } finally {
            File::deleteDirectory($directory);
            File::delete($upload);
        }
    }

    public function test_order_and_order_payment_retries_do_not_duplicate_records(): void
    {
        $data = ['request_key' => (string) Str::uuid(), 'customer_name' => 'Customer', 'customer_phone' => '+971501234567',
            'ordered_at' => '2026-10-21T10:00', 'order_source' => 'walk_in', 'fulfillment_method' => 'pickup',
            'discount' => '0', 'delivery_fee' => '0', 'payment_amount' => '100', 'payment_method' => 'cash',
            'items' => [['nuskha_name' => 'Blend', 'quantity' => '1', 'unit' => 'bottle', 'unit_price' => '1000']]];
        $this->post(route('orders.store'), $data)->assertSessionHasNoErrors();
        $this->post(route('orders.store'), $data)->assertSessionHasNoErrors();
        $this->assertSame(1, NuskhaOrder::count());
        $order = NuskhaOrder::firstOrFail();
        $this->assertSame(1, $order->payments()->count());
        $payment = ['request_key' => (string) Str::uuid(), 'amount' => '100', 'method' => 'cash', 'paid_at' => '2026-10-21T10:01'];
        $this->post(route('orders.payments.store', $order), $payment)->assertSessionHasNoErrors();
        $this->post(route('orders.payments.store', $order), $payment)->assertSessionHasNoErrors();
        $this->assertSame(2, $order->payments()->count());
        $this->assertSame(200.0, (float) $order->payments()->sum('amount'));
    }

    public function test_accountant_cannot_read_recipes_but_can_edit_payments(): void
    {
        $data = $this->billPayload();
        $data['amount'] = '100';
        $data['method'] = 'cash';
        $this->post(route('payments.store'), $data)->assertSessionHasNoErrors();
        $payment = Payment::firstOrFail();
        $this->asRole('Accountant');
        $this->get(route('prescriptions.templates.edit', $this->template))->assertForbidden();
        $this->get(route('prescriptions.index'))->assertForbidden();
        $this->get(route('payments.edit', $payment))->assertOk();
        $this->delete(route('payments.destroy', $payment))->assertForbidden();
    }

    public function test_backup_restore_rejects_broken_references_and_rolls_back(): void
    {
        $directory = storage_path('framework/testing/backups-'.Str::uuid());
        config(['backups.path' => $directory]);
        try {
            $backups = app(DatabaseBackupService::class);
            $path = $backups->create();
            $payload = json_decode(Crypt::decryptString(File::get($path)), true);
            $payload['tables']['patients']['rows'][0]['clinic_id'] = 999999;
            File::put($path, Crypt::encryptString(json_encode($payload)));
            try {
                $backups->restore($path);
                $this->fail('Invalid foreign key must fail.');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('foreign key', $exception->getMessage());
            }
            $this->assertDatabaseHas('patients', ['patient_id' => $this->patient, 'clinic_id' => $this->clinic, 'full_name' => 'Patient One']);
        } finally {
            File::deleteDirectory($directory);
        }
    }

    public function test_backup_and_restore_ignore_tables_in_other_databases(): void
    {
        $original = DB::getDefaultConnection();
        $directory = storage_path('framework/testing/backups-'.Str::uuid());
        config(['backups.path' => $directory, 'database.connections.backup_scope_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        DB::setDefaultConnection('backup_scope_test');
        try {
            DB::statement('CREATE TABLE patients (patient_id INTEGER PRIMARY KEY, full_name TEXT NOT NULL)');
            DB::table('patients')->insert(['patient_id' => 1, 'full_name' => 'Clinic patient']);
            DB::statement("ATTACH DATABASE ':memory:' AS school");
            DB::statement('CREATE TABLE school.academic_sessions (id INTEGER PRIMARY KEY, name TEXT NOT NULL)');
            DB::table('school.academic_sessions')->insert(['id' => 1, 'name' => 'School data']);
            $backups = app(DatabaseBackupService::class);
            $path = $backups->create();
            $this->assertSame(['patients'], array_keys($backups->inspect($path)['tables']));
            DB::table('patients')->update(['full_name' => 'Changed']);
            $backups->restore($path);
            $this->assertSame('Clinic patient', DB::table('patients')->value('full_name'));
            $this->assertSame('School data', DB::table('school.academic_sessions')->value('name'));
        } finally {
            DB::setDefaultConnection($original);
            DB::purge('backup_scope_test');
            File::deleteDirectory($directory);
        }
    }
}
