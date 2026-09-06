<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number')->nullable()->after('payment_method');
                $table->string('courier_name')->nullable()->after('tracking_number');
                $table->string('tracking_url')->nullable()->after('courier_name');
            }
            if (!Schema::hasColumn('orders', 'seller_notes')) {
                $table->text('seller_notes')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('orders', 'processing_at')) {
                $table->timestamp('processing_at')->nullable()->after('paid_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'tracking_number', 'courier_name', 'tracking_url',
                'seller_notes', 'processing_at',
            ]);
        });
    }
};
