<?php

namespace App\Console\Commands;

use App\Models\Filter;
use App\Models\Playlist;
use App\Models\Url;
use App\Models\UrlBackup;
use App\Models\UrlError;
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
    protected $signature = 'app:omdb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh movies notation from OMDB';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseManager $manager)
    {
        $nbError = 0;
        $this->setWalJournalMode(
            $db = $this->getDatabase($manager, 'sqlite')
        );
        $startTime = microtime(true);
        $this->info('Refresh OMDB');
        foreach (Url::whereNull("imdb")->where("tvchannel","!=","1")->where("filter","=",0)->get() as $url) {
            $this->refreshImdb($url);
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        $this->info("Finished : " . number_format($executionTime, 2) . " sec with ". $nbError . " errors");
    }

    protected function refreshImdb(Url $url)
    {
        $infos = explode(" (", $url->name);
        $urlImdb = "https://www.omdbapi.com/?apikey=".env("OMDB_KEY")."&t=".urlencode($infos[0]);

        $content = '';
        try{
            $content = file_get_contents($urlImdb);
            $json = json_decode($content, true);
            if (isset($json['Year'])){
                $url->year = $json['Year'];
            }
            if (isset($json['Ratings']['imdbRating'])) {
                $url->note = $json['Ratings']['imdbRating'];
            }
            if (isset($json['Ratings']['imdbVotes'])) {
                $url->votes = $json['Ratings']['imdbVotes'];
            }
        }catch (\Exception $e){
            //Do nothing
        }
        $url->imdb = $content;
        $url->save();
    }

    /**
     * Returns the Database Connection
     *
     * @param \Illuminate\Database\DatabaseManager $manager
     * @param string $connection
     * @return SQLiteConnection
     * @throws Exception
     */
    protected function getDatabase(DatabaseManager $manager, string $connection)
    {
        $db = $manager->connection($connection);

        // We will throw an exception if the database is not SQLite
        if(!$db instanceof SQLiteConnection) {
            throw new Exception("The '$connection' connection must be sqlite, [{$db->getDriverName()}] given.");
        }

        return $db;
    }

    /**
     * Sets the Journal Mode to WAL
     *
     * @param  \Illuminate\Database\ConnectionInterface $connection
     * @return bool
     */
    protected function setWalJournalMode(ConnectionInterface $connection)
    {
        return $connection->statement('PRAGMA journal_mode=WAL;');
    }

    /**
     * Returns the current Journal Mode of the Database Connection
     *
     * @param  \Illuminate\Database\ConnectionInterface $connection
     * @return string
     */
    protected function getJournalMode(ConnectionInterface $connection)
    {
        return data_get($connection->select(new Expression('PRAGMA journal_mode')), '0.journal_mode');
    }
}
