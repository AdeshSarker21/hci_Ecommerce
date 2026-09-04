<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('store_name');
            $table->string('store_slug')->unique();
            $table->text('store_description')->nullable();
            $table->string('store_logo')->nullable();
            $table->string('store_banner')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->string('contact_website')->nullable();
            $table->text('business_address')->nullable();
            $table->string('business_city', 100)->nullable();
            $table->string('business_state', 100)->nullable();
            $table->string('business_country', 100)->nullable();
            $table->string('business_postal_code', 20)->nullable();
            $table->string('business_registration_number')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(10.00);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('is_active');
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
