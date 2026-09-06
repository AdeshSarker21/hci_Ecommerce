<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->foreignId('commission_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('order_amount', 12, 2);
            $table->decimal('commission_amount', 12, 2);
            $table->decimal('seller_earnings', 12, 2);
            $table->string('commission_type'); // percentage, fixed
            $table->decimal('commission_value', 12, 2); // the rule value used
            $table->string('status')->default('pending'); // pending, settled, cancelled
            $table->string('currency', 3)->default('USD');
            $table->text('notes')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->unique('order_id'); // one commission per order
            $table->index(['seller_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_records');
    }
};
