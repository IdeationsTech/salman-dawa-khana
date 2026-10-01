<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->foreignId('current_license_id')
                ->nullable()
                ->constrained(
                    table: 'clinic_licenses',
                    column: 'clinic_license_id'
                )
                ->nullOnDelete();
        });

        // Jis clinic ki sirf ek existing license hai,
        // usi ko current license mark kar dein.
        $licenses = DB::table('clinic_licenses')
            ->select('clinic_id')
            ->selectRaw('MAX(clinic_license_id) AS license_id')
            ->groupBy('clinic_id')
            ->havingRaw('COUNT(*) = 1')
            ->get();

        foreach ($licenses as $license) {
            DB::table('clinics')
                ->where('clinic_id', $license->clinic_id)
                ->update([
                    'current_license_id' => $license->license_id,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_license_id');
        });
    }
};