<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('website')->nullable();

            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->string('api_base_url')->nullable();
            $table->json('api_config')->nullable();
            $table->json('webhook_config')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->decimal('cod_fee', 8, 2)->default(0);
            $table->decimal('cod_percentage', 5, 2)->default(0);
            $table->decimal('weight_limit_kg', 8, 2)->nullable();
            $table->decimal('max_value', 12, 2)->nullable();
            $table->json('supported_areas')->nullable();
            $table->json('config')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
