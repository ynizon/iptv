<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The number of episodes of the series shown, counted by name
     */
    public function up(): void
    {
        Schema::table('urls', function (Blueprint $table) {
            $table->index('short_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('urls', function (Blueprint $table) {
            $table->dropIndex(['short_name']);
        });
    }
};
