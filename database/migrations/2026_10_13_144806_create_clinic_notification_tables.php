<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | In-App Notifications — Tables
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        Schema::create('clinic_notifications', function (Blueprint $table) {
            $table->bigIncrements('clinic_notification_id');

            $table->unsignedBigInteger('clinic_id');

            $table->string('type', 40);

            // Unique event key prevents duplicate alerts.
            $table->string('event_key', 120);

            $table->string('title', 160);
            $table->text('message');

            // Store route information rather than a fixed hostname.
            $table->string('route_name', 100)->nullable();
            $table->json('route_parameters')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('clinics')
                ->cascadeOnDelete();

            $table->unique(
                ['clinic_id', 'event_key'],
                'clinic_notifications_event_unique'
            );

            $table->index(
                ['clinic_id', 'created_at'],
                'clinic_notifications_date_index'
            );
        });

        Schema::create('clinic_notification_reads', function (Blueprint $table) {
            $table->bigIncrements('clinic_notification_read_id');

            $table->unsignedBigInteger('clinic_notification_id');
            $table->unsignedBigInteger('user_id');

            $table->timestamp('read_at')->useCurrent();

            $table->foreign('clinic_notification_id')
                ->references('clinic_notification_id')
                ->on('clinic_notifications')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();

            $table->unique(
                ['clinic_notification_id', 'user_id'],
                'clinic_notification_reads_unique'
            );

            $table->index(
                ['user_id', 'clinic_notification_id'],
                'clinic_notification_reads_user_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_notification_reads');
        Schema::dropIfExists('clinic_notifications');
    }
};