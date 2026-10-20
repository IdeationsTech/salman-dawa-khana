<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'platform_admin_password_reset_tokens',
            function (Blueprint $table) {
                $table->string('email', 160)->primary();
                $table->string('token', 255);
                $table->timestamp('created_at')->nullable()->index();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_admin_password_reset_tokens');
    }
};