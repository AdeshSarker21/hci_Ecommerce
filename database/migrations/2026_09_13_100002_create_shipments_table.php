<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('courier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('seller_id')->nullable()->constrained()->nullOnDelete();

            $table->string('shipment_number')->unique();
            $table->string('consignment_id')->nullable()->index();
            $table->string('tracking_code')->nullable()->index();
            $table->string('invoice_number')->nullable();

            $table->string('status')->default('pending')->index();
            $table->string('previous_status')->nullable();
            $table->text('status_details')->nullable();
            $table->json('status_history')->nullable();

            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->text('recipient_address');
            $table->string('recipient_city')->nullable();
            $table->string('recipient_area')->nullable();
            $table->string('recipient_zone')->nullable();

            $table->decimal('cod_amount', 12, 2)->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('cod_fee', 12, 2)->default(0);
            $table->decimal('total_collectable', 12, 2)->default(0);
            $table->decimal('total_delivered', 12, 2)->default(0);
            $table->decimal('total_returned', 12, 2)->default(0);
            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->integer('quantity')->default(1);

            $table->string('courier_name')->nullable();
            $table->string('courier_status')->nullable();
            $table->json('courier_response')->nullable();
            $table->json('courier_data')->nullable();

            $table->string('pickup_address')->nullable();
            $table->string('pickup_area')->nullable();
            $table->string('delivery_type')->default('cod');
            $table->string('note')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamp('pickup_date')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('in_transit_at')->nullable();
            $table->timestamp('out_for_delivery_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['seller_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
