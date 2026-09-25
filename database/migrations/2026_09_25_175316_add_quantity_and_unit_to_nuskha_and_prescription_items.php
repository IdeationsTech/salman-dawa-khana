<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nuskha_items', function (Blueprint $table) {
            $table->decimal('quantity', 12, 3)
                ->nullable()
                ->after('item_name');

            $table->string('unit', 20)
                ->nullable()
                ->after('quantity');
        });

        Schema::table('prescription_items', function (Blueprint $table) {
            $table->decimal('quantity', 12, 3)
                ->nullable()
                ->after('item_name');

            $table->string('unit', 20)
                ->nullable()
                ->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'unit']);
        });

        Schema::table('nuskha_items', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'unit']);
        });
    }
};