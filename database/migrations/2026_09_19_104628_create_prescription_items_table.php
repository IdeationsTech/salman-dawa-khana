<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id('prescription_item_id');

            $table->unsignedBigInteger('prescription_id');
            $table->string('item_name', 160);
            $table->string('dosage', 80)->nullable();
            $table->string('frequency', 80)->nullable();
            $table->string('duration', 80)->nullable();
            $table->string('timing', 80)->nullable();
            $table->string('instructions', 255)->nullable();
            $table->integer('sort_order')->default(1);

            $table->foreign('prescription_id')
                ->references('prescription_id')
                ->on('prescriptions');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};