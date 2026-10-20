<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Separate assignments from historical prescription ingredient rows.
        // Never infer a finished nuskha's quantity by summing ingredients.
        Schema::create('prescription_nuskhas', function (Blueprint $table) {
            $table->id('prescription_nuskha_id');
            $table->unsignedBigInteger('prescription_id');
            $table->unsignedBigInteger('nuskha_template_id')->nullable();
            $table->string('nuskha_name', 160);
            $table->decimal('quantity', 12, 3);
            $table->string('unit', 20);
            $table->string('frequency', 80)->nullable();
            $table->string('duration', 80)->nullable();
            $table->string('timing', 80)->nullable();
            $table->string('instructions', 255)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();

            $table->foreign('prescription_id')
                ->references('prescription_id')->on('prescriptions')
                ->cascadeOnDelete();
            $table->foreign('nuskha_template_id')
                ->references('nuskha_template_id')->on('nuskha_templates')
                ->nullOnDelete();
            $table->index(['prescription_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_nuskhas');
    }
};
