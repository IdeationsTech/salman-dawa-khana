<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->string('status', 30)->nullable();

            $table->index(
                ['clinic_id', 'status', 'visit_date'],
                'visits_clinic_status_date_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropIndex('visits_clinic_status_date_index');
            $table->dropColumn('status');
        });
    }
};