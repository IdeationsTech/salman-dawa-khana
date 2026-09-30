<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id('subscription_plan_id');
            $table->string('plan_code', 40)->unique();
            $table->string('name', 120);
            $table->string('term_type', 20);
            $table->unsignedSmallInteger('duration_days')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->char('currency', 3)->default('AED');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};