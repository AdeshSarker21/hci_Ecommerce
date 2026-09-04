<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();

            // Names
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('description_bn')->nullable();

            // Product type & SKU
            $table->string('type', 50)->default('physical');
            $table->string('sku', 100)->nullable()->unique();

            // Pricing
            $table->decimal('price', 12, 2);
            $table->decimal('compare_at_price', 12, 2)->nullable();
            $table->decimal('cost_price', 12, 2)->nullable();

            // Stock
            $table->integer('quantity')->default(0);
            $table->boolean('manage_stock')->default(true);
            $table->integer('low_stock_threshold')->default(5);

            // Status
            $table->string('status', 30)->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('approved_at')->nullable();

            // Flags
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('type');
            $table->index(['seller_id', 'status']);
            $table->index('category_id');
            $table->index('brand_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
