<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;

class RefreshCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild the cache of every category page';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        $this->info('Cache category pages');
        $nb = Category::warmPages();
        $this->info("Finished : " . $nb . " pages in " . number_format(microtime(true) - $startTime, 2) . " sec");
    }
}
