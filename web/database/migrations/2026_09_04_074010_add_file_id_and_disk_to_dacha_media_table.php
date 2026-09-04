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
        Schema::table('dacha_media', function (Blueprint $table) {
            $table->string('file_id')->nullable()->after('path');
            $table->string('disk')->default('public')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dacha_media', function (Blueprint $table) {
            $table->dropColumn(['file_id', 'disk']);
        });
    }
};
