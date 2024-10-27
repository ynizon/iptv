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
        Schema::table('urls', function($table) {
            $table->integer('note')->default(-1);
        });
        Schema::table('urls', function($table) {
            $table->integer('votes')->default(-1);
        });
        Schema::table('urls', function($table) {
            $table->integer('year')->default(0);
        });
        Schema::table('urls', function($table) {
            $table->string('imdb')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('urls', function($table) {
            $table->dropColumn('note');
            $table->dropColumn('votes');
            $table->dropColumn('imdb');
            $table->dropColumn('year');
        });
    }
};
