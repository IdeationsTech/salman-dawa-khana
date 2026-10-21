<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('user_id');
            $table->uuid('request_key');
            $table->string('payload_hash', 64);
            $table->text('redirect_url');
            $table->timestamp('created_at');
            $table->unique(['clinic_id', 'request_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_receipts');
    }
};
