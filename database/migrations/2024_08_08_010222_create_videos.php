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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('channel_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->string('url');

            $table->text('thumbnail')->nullable();
            $table->string('username')->nullable();
            $table->text('watermarked_video')->nullable();
            $table->string('watermark_free_video')->nullable();

            $table->boolean('processed')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
