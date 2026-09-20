<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id('permission_id');
            $table->string('code', 80)->unique();
            $table->string('label', 120);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};