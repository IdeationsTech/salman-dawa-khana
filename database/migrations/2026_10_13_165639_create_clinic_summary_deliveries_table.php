<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_summary_deliveries', function (Blueprint $table) {
            $table->bigIncrements('clinic_summary_delivery_id');

            $table->unsignedBigInteger('clinic_id');

            $table->date('summary_date');

            // Keep local log testing separate from actual email sending.
            $table->string('delivery_channel', 40);

            $table->string('recipient_email', 160);

            // pending, processing, sent or failed
            $table->string('status', 20)->default('pending');

            $table->unsignedInteger('attempts')->default(0);

            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->text('last_error')->nullable();

            $table->timestamps();

            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('clinics')
                ->cascadeOnDelete();

            $table->unique(
                [
                    'clinic_id',
                    'summary_date',
                    'delivery_channel',
                ],
                'clinic_summary_delivery_unique'
            );

            $table->index(
                ['status', 'processing_started_at'],
                'clinic_summary_delivery_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_summary_deliveries');
    }
};