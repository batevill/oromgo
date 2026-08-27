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
        Schema::table('regions', function (Blueprint $table) {
            $table->unsignedBigInteger('soato_id')->nullable()->after('id')->index();
            $table->string('name_uz')->nullable()->after('soato_id');
            $table->string('name_oz')->nullable()->after('name_uz');
            $table->string('name_ru')->nullable()->after('name_oz');
            $table->string('name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn(['soato_id', 'name_uz', 'name_oz', 'name_ru']);
        });
    }
};
