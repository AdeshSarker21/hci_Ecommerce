<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_staff', function (Blueprint $table) {
            $table->string('name')->after('user_id')->nullable();
            $table->string('email')->after('name')->nullable();
            $table->boolean('is_active')->default(true)->after('can_view_reports');
            $table->text('notes')->nullable()->after('is_active');
            $table->timestamp('last_active_at')->nullable()->after('joined_at');

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('seller_staff', function (Blueprint $table) {
            $table->dropColumn(['name', 'email', 'is_active', 'notes', 'last_active_at']);
        });
    }
};
