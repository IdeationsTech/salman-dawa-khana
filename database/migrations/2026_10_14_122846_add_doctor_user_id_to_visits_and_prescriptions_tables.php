<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->unsignedBigInteger('doctor_user_id')
                ->nullable()
                ->after('recorded_by_user_id');

            $table->foreign('doctor_user_id')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('doctor_user_id')
                ->nullable()
                ->after('created_by_user_id');

            $table->foreign('doctor_user_id')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropForeign(['doctor_user_id']);
            $table->dropColumn('doctor_user_id');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->dropForeign(['doctor_user_id']);
            $table->dropColumn('doctor_user_id');
        });
    }
};