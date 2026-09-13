<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shipment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type')->nullable();
            $table->string('courier_slug');
            $table->string('consignment_id')->nullable();
            $table->json('payload')->nullable();
            $table->json('headers')->nullable();
            $table->string('ip_address')->nullable();
            $table->boolean('is_processed')->default(false);
            $table->text('processing_result')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['courier_slug', 'consignment_id']);
            $table->index('is_processed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_webhook_logs');
    }
};
