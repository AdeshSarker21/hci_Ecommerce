<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courier_collections', function (Blueprint $table) {
            $table->foreignId('courier_id')->nullable()->after('seller_id')->constrained()->nullOnDelete();

            $table->decimal('courier_charge', 12, 2)->default(0)->after('cod_amount');
            $table->decimal('net_collected', 12, 2)->default(0)->after('courier_charge');
            $table->decimal('marketplace_earning', 12, 2)->default(0)->after('seller_earning');

            $table->string('reconciliation_status', 30)->default('pending')->after('settlement_status')->index();
            $table->string('reconciliation_reference')->nullable()->after('reconciliation_status');
            $table->timestamp('reconciled_at')->nullable()->after('reconciliation_reference');
            $table->foreignId('reconciled_by')->nullable()->after('reconciled_at')->constrained('users')->nullOnDelete();

            $table->string('courier_payment_reference')->nullable()->after('reconciled_by');
            $table->timestamp('courier_payment_date')->nullable()->after('courier_payment_reference');

            $table->decimal('discrepancy_amount', 12, 2)->nullable()->after('courier_payment_date');
            $table->text('discrepancy_notes')->nullable()->after('discrepancy_amount');

            $table->string('import_batch_id', 100)->nullable()->after('discrepancy_notes')->index();
            $table->json('raw_courier_data')->nullable()->after('import_batch_id');

            $table->text('admin_notes')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('courier_collections', function (Blueprint $table) {
            $table->dropForeign(['courier_id']);
            $table->dropColumn([
                'courier_id', 'courier_charge', 'net_collected', 'marketplace_earning',
                'reconciliation_status', 'reconciliation_reference', 'reconciled_at', 'reconciled_by',
                'courier_payment_reference', 'courier_payment_date',
                'discrepancy_amount', 'discrepancy_notes',
                'import_batch_id', 'raw_courier_data', 'admin_notes',
            ]);
        });
    }
};
