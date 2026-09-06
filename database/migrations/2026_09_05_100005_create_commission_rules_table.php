<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // percentage, fixed
            $table->decimal('value', 12, 2); // percentage (0-100) or fixed amount
            $table->string('applies_to')->default('global'); // global, seller, category, product
            $table->foreignId('seller_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // higher = checked first
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['applies_to', 'is_active']);
            $table->index(['seller_id', 'is_active']);
            $table->index(['category_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_rules');
    }
};
