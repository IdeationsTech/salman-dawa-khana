<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Nuskha Orders — Customer and Order Details
        |--------------------------------------------------------------------------
        */

        Schema::create('nuskha_orders', function (Blueprint $table) {
            $table->bigIncrements('nuskha_order_id');

            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('created_by_user_id')->nullable();

            $table->string('order_no', 40);
            $table->dateTime('ordered_at');

            $table->string('customer_name', 160);
            $table->string('customer_phone', 30);

            // walk_in or phone
            $table->string('order_source', 20);

            // pickup or delivery
            $table->string('fulfillment_method', 20);
            $table->string('delivery_address', 500)->nullable();

            // confirmed, preparing, ready, completed or cancelled
            $table->string('status', 20)->default('confirmed');

            // Snapshot currency for this order.
            $table->char('currency', 3);

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('clinics')
                ->restrictOnDelete();

            $table->foreign('created_by_user_id')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();

            $table->unique(
                ['clinic_id', 'order_no'],
                'nuskha_orders_number_unique'
            );

            $table->index(
                ['clinic_id', 'ordered_at'],
                'nuskha_orders_date_index'
            );

            $table->index(
                ['clinic_id', 'status'],
                'nuskha_orders_status_index'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Nuskha Orders — Ordered Items
        |--------------------------------------------------------------------------
        */

        Schema::create('nuskha_order_items', function (Blueprint $table) {
            $table->bigIncrements('nuskha_order_item_id');

            $table->unsignedBigInteger('nuskha_order_id');
            $table->unsignedBigInteger('nuskha_template_id')->nullable();

            // Preserve the sold name even if the template is edited.
            $table->string('nuskha_name', 160);

            // Customer-facing preparation/packing instructions.
            $table->string('instructions', 500)->nullable();

            // Internal recipe snapshot; not shown on customer receipt.
            $table->json('recipe_snapshot')->nullable();

            $table->decimal('quantity', 12, 3);
            $table->string('unit', 20);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);

            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();

            $table->foreign('nuskha_order_id')
                ->references('nuskha_order_id')
                ->on('nuskha_orders')
                ->cascadeOnDelete();

            $table->foreign('nuskha_template_id')
                ->references('nuskha_template_id')
                ->on('nuskha_templates')
                ->nullOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | Nuskha Orders — Received Payments
        |--------------------------------------------------------------------------
        */

        Schema::create('nuskha_order_payments', function (Blueprint $table) {
            $table->bigIncrements('nuskha_order_payment_id');

            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('nuskha_order_id');
            $table->unsignedBigInteger('received_by_user_id')->nullable();

            $table->dateTime('paid_at');
            $table->decimal('amount', 12, 2);

            // cash, bank or card
            $table->string('method', 20);
            $table->string('reference_no', 100)->nullable();
            $table->string('notes', 500)->nullable();

            $table->timestamps();

            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('clinics')
                ->restrictOnDelete();

            $table->foreign('nuskha_order_id')
                ->references('nuskha_order_id')
                ->on('nuskha_orders')
                ->restrictOnDelete();

            $table->foreign('received_by_user_id')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();

            $table->index(
                ['clinic_id', 'paid_at'],
                'nuskha_order_payments_date_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nuskha_order_payments');
        Schema::dropIfExists('nuskha_order_items');
        Schema::dropIfExists('nuskha_orders');
    }
};