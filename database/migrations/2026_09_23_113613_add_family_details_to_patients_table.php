<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('marital_status', 20)->nullable();
            $table->boolean('has_children')->nullable();
            $table->unsignedSmallInteger('children_count')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'marital_status',
                'has_children',
                'children_count',
            ]);
        });
    }
};