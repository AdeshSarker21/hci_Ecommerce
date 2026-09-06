<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->decimal('pending_balance', 12, 2)->default(0);
            $table->decimal('available_balance', 12, 2)->default(0);
            $table->decimal('withdrawn_amount', 12, 2)->default(0);
            $table->decimal('total_earned', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->timestamps();

            $table->unique('seller_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
