<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visible_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Start the whitelist with the categories visible today
        $now = date("Y-m-d H:i:s");
        $names = DB::table('urls')->where("filter", "=", 0)->distinct()->pluck('category');
        foreach ($names->chunk(100) as $chunk) {
            DB::table('visible_categories')->insert($chunk->map(fn ($name) => [
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ])->values()->all());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visible_categories');
    }
};
