<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->string('value_bn')->nullable();
            $table->string('slug')->nullable();
            $table->string('color_code', 20)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['attribute_id', 'value']);
            $table->index('attribute_id');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_values');
    }
};
