<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->foreignId('commission_record_id')->nullable()->constrained()->nullOnDelete();

            $table->string('courier_name')->default('steadfast');
            $table->string('consignment_id')->nullable();
            $table->decimal('cod_amount', 12, 2);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('seller_earning', 12, 2)->default(0);

            $table->string('collection_status')->default('pending')->index();
            $table->timestamp('collected_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();

            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['seller_id', 'collection_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_collections');
    }
};
