<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('role', 50)->default('staff');
            $table->boolean('can_manage_products')->default(false);
            $table->boolean('can_manage_orders')->default(false);
            $table->boolean('can_manage_settings')->default(false);
            $table->boolean('can_view_reports')->default(false);
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['seller_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_staff');
    }
};
