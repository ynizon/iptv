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
        Schema::create('urls', function (Blueprint $table) {
            $table->id();
            $table->string('url')->unique();
            $table->string('name')->index();
            $table->string('short_name')->default('');
            $table->string('picture');
            $table->string('category');
            $table->integer('tvchannel')->default(0);
            $table->integer('serie')->default(0);
            $table->integer('movie')->default(0);
            $table->integer('season')->default(0);
            $table->integer('episod')->default(0);
            $table->integer('filter')->default(0);
            $table->integer('note')->default(-1);
            $table->integer('votes')->default(-1);
            $table->integer('year')->default(0);
            $table->string('imdb')->nullable();

            $table->timestamps();
            $table->unsignedBigInteger('playlist_id');
            $table->foreign('playlist_id')->references('id')->on('playlists')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('urls');
    }
};
