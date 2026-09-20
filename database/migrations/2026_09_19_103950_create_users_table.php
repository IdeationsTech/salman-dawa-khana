<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');

            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('role_id');

            $table->string('name', 120);
            $table->string('email', 160);
            $table->string('password', 255);
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at');

            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('clinics');

            $table->foreign('role_id')
                ->references('role_id')
                ->on('roles');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};