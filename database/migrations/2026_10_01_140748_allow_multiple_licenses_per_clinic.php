<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Preserve an index for the clinic foreign key.
        Schema::table('clinic_licenses', function (Blueprint $table) {
            $table->index(
                'clinic_id',
                'clinic_licenses_clinic_id_index'
            );
        });

        // Permit separate historical licenses for one clinic.
        Schema::table('clinic_licenses', function (Blueprint $table) {
            $table->dropUnique(
                'clinic_licenses_clinic_id_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('clinic_licenses', function (Blueprint $table) {
            $table->unique(
                'clinic_id',
                'clinic_licenses_clinic_id_unique'
            );
        });

        Schema::table('clinic_licenses', function (Blueprint $table) {
            $table->dropIndex(
                'clinic_licenses_clinic_id_index'
            );
        });
    }
};