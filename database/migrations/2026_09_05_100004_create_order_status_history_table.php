<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_status_history')) {
            Schema::create('order_status_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('from_status')->nullable();
                $table->string('to_status');
                $table->string('type')->default('status');
                $table->text('note')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['order_id', 'created_at']);
                $table->index('to_status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
    }
};
