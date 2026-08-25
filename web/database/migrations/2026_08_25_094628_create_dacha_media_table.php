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
        Schema::create('dacha_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dacha_id')->constrained('dachas')->cascadeOnDelete();
            $table->enum('type', ['image', 'video'])->default('image');
            $table->string('path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dacha_media');
    }
};
