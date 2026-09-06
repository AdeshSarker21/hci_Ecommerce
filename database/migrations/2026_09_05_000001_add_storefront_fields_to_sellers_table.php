<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->text('store_description_bn')->nullable()->after('store_description');
            $table->string('store_tagline')->nullable()->after('store_description_bn');
            $table->string('store_tagline_bn')->nullable()->after('store_tagline');
            $table->text('shipping_policy')->nullable()->after('store_tagline_bn');
            $table->text('shipping_policy_bn')->nullable()->after('shipping_policy');
            $table->text('return_policy')->nullable()->after('shipping_policy_bn');
            $table->text('return_policy_bn')->nullable()->after('return_policy');
            $table->text('about_us')->nullable()->after('return_policy_bn');
            $table->text('about_us_bn')->nullable()->after('about_us');
            $table->string('facebook_url')->nullable()->after('about_us_bn');
            $table->string('instagram_url')->nullable()->after('facebook_url');
            $table->string('youtube_url')->nullable()->after('instagram_url');
            $table->decimal('average_rating', 3, 2)->default(0)->after('youtube_url');
            $table->unsignedInteger('total_reviews')->default(0)->after('average_rating');
            $table->unsignedInteger('total_sales')->default(0)->after('total_reviews');
        });
    }

    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn([
                'store_description_bn', 'store_tagline', 'store_tagline_bn',
                'shipping_policy', 'shipping_policy_bn',
                'return_policy', 'return_policy_bn',
                'about_us', 'about_us_bn',
                'facebook_url', 'instagram_url', 'youtube_url',
                'average_rating', 'total_reviews', 'total_sales',
            ]);
        });
    }
};
