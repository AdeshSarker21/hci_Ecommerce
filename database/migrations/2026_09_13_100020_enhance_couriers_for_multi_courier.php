<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('couriers', function (Blueprint $table) {
            $table->text('webhook_secret')->nullable()->after('api_config');
            $table->json('supported_services')->nullable()->after('webhook_config');
            $table->boolean('cod_support')->default(true)->after('is_active');
            $table->boolean('tracking_support')->default(true)->after('cod_support');
            $table->boolean('shipment_creation_support')->default(true)->after('tracking_support');
            $table->boolean('return_support')->default(true)->after('shipment_creation_support');
            $table->text('access_token')->nullable()->after('api_secret');
            $table->timestamp('token_expires_at')->nullable()->after('access_token');
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->string('internal_status')->default('pending')->after('status')->index();
        });

        Schema::table('courier_webhook_logs', function (Blueprint $table) {
            $table->string('idempotency_key')->nullable()->after('ip_address')->unique();
            $table->timestamp('processed_at')->nullable()->after('error_message');
        });
    }

    public function down(): void
    {
        Schema::table('couriers', function (Blueprint $table) {
            $table->dropColumn([
                'webhook_secret', 'supported_services', 'cod_support',
                'tracking_support', 'shipment_creation_support', 'return_support',
                'access_token', 'token_expires_at',
            ]);
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn('internal_status');
        });

        Schema::table('courier_webhook_logs', function (Blueprint $table) {
            $table->dropColumn(['idempotency_key', 'processed_at']);
        });
    }
};
