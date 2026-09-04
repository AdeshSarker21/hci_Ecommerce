<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->string('status', 20)->default('active')->after('avatar');
            $table->boolean('is_active')->default(true)->after('status');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('timezone', 50)->nullable()->after('last_login_at');
            $table->string('locale', 10)->default('en')->after('timezone');

            $table->index('status');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'avatar', 'status', 'is_active',
                'last_login_at', 'timezone', 'locale',
            ]);
        });
    }
};
