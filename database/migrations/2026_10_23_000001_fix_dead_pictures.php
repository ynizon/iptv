<?php

use App\Models\Url;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManager;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Series posters on the dead provider hosts -> same posters on TMDB
        DB::table('urls')->select('id', 'picture')->where('picture', 'like', '%/images/series/%')
            ->chunkById(1000, function ($urls) {
                foreach ($urls as $url) {
                    $picture = Url::normalizePicture($url->picture);
                    if ($picture != $url->picture) {
                        DB::table('urls')->where('id', $url->id)->update(['picture' => $picture]);
                    }
                }
            });

        // Remove the default picture cached when a download failed, so it is tried again
        if (extension_loaded('gd')) {
            $manager = new ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            try {
                $default = md5((string) $manager->read(file_get_contents(public_path('images/default.webp')))
                    ->resize(120, 180)->toJpg());
            } catch (\Throwable $e) {
                // GD without WebP support: only a clean up, it must not block the startup
                return;
            }
            foreach (glob(public_path('images/playlist/*/*.jpg')) ?: [] as $file) {
                if (md5_file($file) === $default) {
                    unlink($file);
                }
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
