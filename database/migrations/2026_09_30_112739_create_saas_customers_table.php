<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_customers', function (Blueprint $table) {
            $table->id('saas_customer_id');
            $table->string('contact_name', 120);
            $table->string('company_name', 160)->nullable();
            $table->string('email', 160)->index();
            $table->string('phone', 30)->nullable();
            $table->char('country_code', 2)->nullable();
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_customers');
    }
};