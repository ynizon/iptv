<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Channel logos were cached as stretched 120x180 posters: remove them,
     * /picture now makes 16:9 tiles for the channels
     */
    public function up(): void
    {
        foreach (DB::table('urls')->where('tvchannel', 1)->pluck('id') as $id) {
            $file = public_path("images/playlist/$id/$id.jpg");
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
