<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->string('slug')->unique();
            $table->string('type', 50)->default('text');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->boolean('is_variant')->default(false);
            $table->string('status', 20)->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('sort_order');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
