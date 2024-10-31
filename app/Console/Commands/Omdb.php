<?php

namespace App\Console\Commands;

use App\Helpers\Sqlite;
use App\Models\Url;
use App\Models\UrlBackup;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\SQLiteConnection;

class Omdb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh:omdb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh movies notation from OMDB';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseManager $manager, Sqlite $sqlite)
    {
        $nbError = 0;
        $sqlite->setWalJournalMode(
            $db = $sqlite->getDatabase($manager, 'sqlite')
        );
        $startTime = microtime(true);
        $this->info('Refresh OMDB');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Récupère la réponse sans l'afficher
        curl_setopt($ch, CURLOPT_HEADER, false);           // Inclut les en-têtes dans la sortie
        curl_setopt($ch, CURLOPT_NOBODY, false);           // N'inclut que les en-têtes dans la réponse

        //$bar = $this->output->createProgressBar(Url::whereNull("imdb")->where("tvchannel","!=","1")->where("filter","=",0)->count());
        $nb = 0;
        foreach (Url::whereNull("imdb")->where("tvchannel","!=","1")->where("filter","=",0)->get() as $url) {
//            $bar->advance();
            $this->refreshOmdb($ch, $url, $nb);
            $nb++;
        }

        curl_close($ch);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        $this->info("Finished : " . number_format($executionTime, 2) . " sec with ". $nbError . " errors");
    }

    protected function refreshOmdb($ch, Url $url, $nb)
    {
        try{
            $infos = explode(" (", $url->name);
            $urlImdb = "https://www.omdbapi.com/?apikey=".env("OMDB_KEY")."&t=".urlencode($infos[0]);

            curl_setopt($ch, CURLOPT_URL, $urlImdb);
            $content = curl_exec($ch);
            $returnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $this->info($url->id. " : ". $urlImdb . " -> ".$returnCode);

            $json = json_decode($content, true);
            if (200 == $returnCode) {
                if (isset($json['Year'])){
                    $url->year = $json['Year'];
                }
                if (isset($json['imdbRating'])) {
                    $url->note = $json['imdbRating'];
                }
                if (isset($json['imdbVotes'])) {
                    $url->votes = str_replace(",","",$json['imdbVotes']);
                }
                $url->imdb = $content;
                $url->save();
            } else {
                $this->error($nb . " urls updated : ". $json['Error']);
                exit();
            }
        }catch (\Exception $e){
            //Do nothing
        }
    }
}
