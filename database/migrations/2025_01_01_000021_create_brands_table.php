<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->string('status', 20)->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
