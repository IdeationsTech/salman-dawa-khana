<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['users', 'platform_admins'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->string('preferred_locale', 2)->nullable());
        }
    }
    public function down(): void
    {
        foreach (['users', 'platform_admins'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn('preferred_locale'));
        }
    }
};
