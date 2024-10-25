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
        Schema::create('url_errors', function (Blueprint $table) {
            $table->id();
            $table->string('url')->unique();
            $table->unsignedBigInteger('playlist_id');
            $table->timestamps();
            $table->foreign('playlist_id')->references('id')->on('playlists')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('url_errors');
    }
};
