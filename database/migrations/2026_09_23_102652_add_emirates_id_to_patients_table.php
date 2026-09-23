<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('emirates_id', 15)
                ->nullable()
                ->after('phone');

            $table->unique(
                ['clinic_id', 'emirates_id'],
                'patients_clinic_emirates_id_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropUnique('patients_clinic_emirates_id_unique');
            $table->dropColumn('emirates_id');
        });
    }
};