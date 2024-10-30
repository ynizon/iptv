<?php

namespace App\Console\Commands;

use App\Helpers\Sqlite;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use VfacTmdb\Factory;
use VfacTmdb\Search;

class RefreshInfo extends Command
{

    protected array $tvChannels = [
        " TV ",
        "FR SPORTS (France)",
        "DAZN",
        "EUROSPORT",
        "NBA PASS",
        "CHAINES.",
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh:info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh informations for movies';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseManager $manager, Sqlite $sqlite)
    {
        $options = ['language'=>'fr-FR'];

        $sqlite->setWalJournalMode(
            $db = $sqlite->getDatabase($manager, 'sqlite')
        );

        $tmdb = Factory::create()->getTmdb(env('TMDB_KEY'));
        $search    = new Search($tmdb);
        $responses = $search->tvshow('Mon oncle Charlie', $options);

        // Get all results
        echo json_encode($responses);
        foreach ($responses as $response)
        {
            echo $response->getTitle()."\n";
        }
        exit();

    }
}
