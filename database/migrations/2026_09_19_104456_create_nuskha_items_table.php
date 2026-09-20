<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nuskha_items', function (Blueprint $table) {
            $table->id('nuskha_item_id');

            $table->unsignedBigInteger('nuskha_template_id');
            $table->string('item_name', 160);
            $table->string('dosage', 80)->nullable();
            $table->string('frequency', 80)->nullable();
            $table->string('duration', 80)->nullable();
            $table->string('timing', 80)->nullable();
            $table->string('instructions', 255)->nullable();
            $table->integer('sort_order')->default(1);

            $table->foreign('nuskha_template_id')
                ->references('nuskha_template_id')
                ->on('nuskha_templates');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nuskha_items');
    }
};