<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courier_collections', function (Blueprint $table) {
            $table->timestamp('delivery_date')->nullable()->after('confirmed_at');
            $table->string('settlement_status')->default('pending')->after('collection_status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('courier_collections', function (Blueprint $table) {
            $table->dropColumn(['delivery_date', 'settlement_status']);
        });
    }
};
