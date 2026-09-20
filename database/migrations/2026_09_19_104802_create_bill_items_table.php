<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_items', function (Blueprint $table) {
            $table->id('bill_item_id');

            $table->unsignedBigInteger('bill_id');
            $table->string('description', 255);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);

            $table->foreign('bill_id')
                ->references('bill_id')
                ->on('bills');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_items');
    }
};