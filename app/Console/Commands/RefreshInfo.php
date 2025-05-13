<?php

namespace App\Console\Commands;

use App\Helpers\Sqlite;
use App\Models\Url;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\DB;
use VfacTmdb\Factory;
use VfacTmdb\Item;
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
        $nbError = 0;
        $options = ['language'=>'fr-FR'];
        $sqlite->setWalJournalMode(
            $db = $sqlite->getDatabase($manager, 'sqlite')
        );
        $startTime = microtime(true);
        if (env("TMDB_KEY") == '') {
            $this->error('Set your TMDB_KEY api in your .env file.');
            exit();
        }
        DB::update("update urls set picture = '' where picture like '%365.tv%'");//Remove error picture

        $this->info('Refresh TMDB');
        $tmdb = Factory::create()->getTmdb(env('TMDB_KEY'));
        $search    = new Search($tmdb);

        $nb = 0;
        $bar = $this->output->createProgressBar(Url::whereNull("imdb")->where("tvchannel","!=","1")
            ->where("filter","=",0)->count());
        foreach (Url::whereNull("imdb")->where("tvchannel","!=","1")->where("filter","=",0)->get() as $url) {
            $bar->advance();
            $name = $url->name;
            $name = preg_replace('/[^\p{L}\p{N}\p{P}\p{Z}]/u', '', $name);
            $pos = stripos($name,"(");

            if ($pos !== false ){
                $name = trim(substr($name,0,$pos));
            }

            try {
                if ($url->movie == 1) {
                    $responses = $search->movie($name, $options);
                } else {
                    $responses = $search->tvshow($name, $options);
                }

                foreach ($responses as $response) {
                    $item = new Item($tmdb);
                    if ($url->movie == 1) {
                        $infos = $item->getMovie($response->getId(), $options);
                        $url->votes = $infos->getNbNotes();
                    }else {
                        $url->votes = 0;
                        $infos = $item->getTVShow($response->getId(), $options);
                    }
                    $url->year = '';
                    if ($infos->getReleaseDate() != '') {
                        $url->year = substr($infos->getReleaseDate(), 0, 4);
                    }
                    $url->note = $infos->getNote();
                    $url->imdb = $infos->getOverview();
                    $url->picture = "https://image.tmdb.org/t/p/w300_and_h450_bestv2/".$infos->getPosterPath();
                    $url->save();
                    break;
                }
            }catch(\Exception $e){
                $nbError++;
                $url->imdb = "-";
                $url->save();
            }

            $nb++;
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        $this->info("");
        $this->info("Finished : " . number_format($executionTime, 2) . " sec with ". $nbError . " errors");
    }
}
