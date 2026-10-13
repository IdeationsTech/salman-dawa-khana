<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->char('national_id_country', 2)
                ->nullable();

            $table->string('national_id_type', 30)
                ->nullable();

            $table->string('national_id_number', 40)
                ->nullable();
        });

        /*
        |--------------------------------------------------------------------------
        | Preserve Existing Emirates IDs
        |--------------------------------------------------------------------------
        */

        DB::table('patients')
            ->whereNotNull('emirates_id')
            ->where('emirates_id', '<>', '')
            ->update([
                'national_id_country' => 'AE',
                'national_id_type' => 'emirates_id',
                'national_id_number' => DB::raw(
                    "REPLACE(REPLACE(emirates_id, '-', ''), ' ', '')"
                ),
            ]);

        Schema::table('patients', function (Blueprint $table) {
            $table->unique(
                [
                    'clinic_id',
                    'national_id_country',
                    'national_id_type',
                    'national_id_number',
                ],
                'patients_clinic_national_id_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropUnique(
                'patients_clinic_national_id_unique'
            );

            $table->dropColumn([
                'national_id_country',
                'national_id_type',
                'national_id_number',
            ]);
        });
    }
};