<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. dachas jadvali uchun indekslar
        Schema::table('dachas', function (Blueprint $table) {
            $table->index(['status', 'region', 'district', 'weekday_price'], 'dachas_status_region_district_price_idx');
            $table->index(['status', 'region_id', 'district_id'], 'dachas_status_region_district_id_idx');
            $table->index(['user_id', 'status'], 'dachas_user_status_idx');
            $table->index(['status', 'capacity'], 'dachas_status_capacity_idx');
            $table->index(['status', 'currency', 'weekday_price'], 'dachas_status_currency_price_idx');
            $table->index('status', 'dachas_status_idx');
        });

        // 2. bookings jadvali uchun indekslar
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['dacha_id', 'status', 'start_date', 'end_date'], 'bookings_dacha_status_dates_idx');
            $table->index(['user_id', 'status'], 'bookings_user_status_idx');
            $table->index(['source', 'status'], 'bookings_source_status_idx');
            $table->index(['start_date', 'end_date'], 'bookings_dates_idx');
            $table->index('status', 'bookings_status_idx');
        });

        // 3. dacha_media jadvali uchun indekslar
        Schema::table('dacha_media', function (Blueprint $table) {
            $table->index(['dacha_id', 'type'], 'dacha_media_dacha_type_idx');
        });

        // 4. reviews jadvali uchun indekslar
        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['dacha_id', 'rating'], 'reviews_dacha_rating_idx');
        });

        // 5. notifications jadvali uchun indekslar
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read', 'created_at'], 'notifications_user_read_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dachas', function (Blueprint $table) {
            $table->dropIndex('dachas_status_region_district_price_idx');
            $table->dropIndex('dachas_status_region_district_id_idx');
            $table->dropIndex('dachas_user_status_idx');
            $table->dropIndex('dachas_status_capacity_idx');
            $table->dropIndex('dachas_status_currency_price_idx');
            $table->dropIndex('dachas_status_idx');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_dacha_status_dates_idx');
            $table->dropIndex('bookings_user_status_idx');
            $table->dropIndex('bookings_source_status_idx');
            $table->dropIndex('bookings_dates_idx');
            $table->dropIndex('bookings_status_idx');
        });

        Schema::table('dacha_media', function (Blueprint $table) {
            $table->dropIndex('dacha_media_dacha_type_idx');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('reviews_dacha_rating_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_read_created_idx');
        });
    }
};
