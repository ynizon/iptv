<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Length in minutes from TMDB (episode length for a series): null not searched yet, 0 unknown.
     * The progress counter stops at the end and marks the title as seen
     */
    public function up(): void
    {
        Schema::table('urls', function (Blueprint $table) {
            $table->unsignedSmallInteger('duration')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('urls', function (Blueprint $table) {
            $table->dropColumn('duration');
        });
    }
};
