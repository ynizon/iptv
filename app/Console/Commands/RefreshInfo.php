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
    protected $signature = 'refresh:info {--limit=0 : Number of urls to process, 0 for all}';

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
        $query = Url::whereNull("imdb")->where("tvchannel", "!=", "1")->where("filter", 0);
        $limit = (int) $this->option('limit');
        $bar = $this->output->createProgressBar($limit > 0 ? min($limit, $query->count()) : $query->count());

        // One TMDB lookup per title: all the episodes of a series share it
        $found = [];
        // chunkById, as "imdb" (the filtered column) changes while iterating
        $query->chunkById(100, function ($urls) use (&$nb, &$nbError, &$found, $limit, $bar, $search, $options, $tmdb) {
            foreach ($urls as $url) {
                if ($limit > 0 && $nb >= $limit) {
                    return false;
                }
                $bar->advance();
                $nb++;

                $isMovie = $url->movie == 1;
                $name = $this->searchName($url->name);
                $key = ($isMovie ? "movie:" : "tv:") . mb_strtolower($name);
                if (!array_key_exists($key, $found)) {
                    try {
                        $found[$key] = $this->findInfos($search, $tmdb, $isMovie, $name, $options);
                    } catch (\Exception $e) {
                        // Left as is, so it is tried again next time
                        $this->error(" " . $url->id . " " . $name . " : " . $e->getMessage());
                        $nbError++;
                        continue;
                    }
                }

                $infos = $found[$key];
                if ($infos === null) {
                    continue;
                }
                $url->votes = $infos['votes'];
                $url->year = $infos['year'];
                $url->note = $infos['note'];
                $url->imdb = $infos['overview'];
                if ($infos['poster'] != '' && $url->picture != $infos['poster']) {
                    $url->picture = $infos['poster'];
                    // Drop the resized copy of the previous picture
                    @unlink(public_path("images/playlist/{$url->id}/{$url->id}.jpg"));
                }
                $url->save();
            }
        });

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        $this->info("");
        $this->info("Finished : " . number_format($executionTime, 2) . " sec with ". $nbError . " errors");
    }

    /**
     * "|FR| The Handmaid’s Tale : La Servante (MULTI) HD S01 E01" -> "The Handmaid's Tale : La Servante"
     */
    protected function searchName(string $name): string
    {
        $name = str_replace(["’", "‘"], "'", $name);
        // Before the cleaning below, which removes the "|"
        $name = preg_replace('/^\|[^|]*\|\s*/', '', $name);
        $name = preg_replace('/[^\p{L}\p{N}\p{P}\p{Z}]/u', '', $name);
        $name = preg_replace('/\s*S\d{2} E\d{2}.*$/', '', $name);
        $pos = stripos($name, "(");
        if ($pos !== false) {
            $name = substr($name, 0, $pos);
        }

        return trim($name);
    }

    /**
     * Search TMDB with the full title, then with each side of "Original title : French title"
     * @return array|null votes, year, note, overview, poster
     */
    protected function findInfos(Search $search, $tmdb, bool $isMovie, string $name, array $options): ?array
    {
        $names = array_unique(array_merge([$name], array_map('trim', explode(" : ", $name))));
        foreach ($names as $searchName) {
            if ($searchName == '') {
                continue;
            }
            $responses = $isMovie ? $search->movie($searchName, $options) : $search->tvshow($searchName, $options);
            foreach ($responses as $response) {
                $item = new Item($tmdb);
                $infos = $isMovie
                    ? $item->getMovie($response->getId(), $options)
                    : $item->getTVShow($response->getId(), $options);

                return [
                    'votes' => $isMovie ? $infos->getNbNotes() : 0,
                    'year' => $infos->getReleaseDate() != '' ? substr($infos->getReleaseDate(), 0, 4) : '',
                    'note' => $infos->getNote(),
                    'overview' => (string) $infos->getOverview(),
                    'poster' => $infos->getPosterPath() != ''
                        ? "https://image.tmdb.org/t/p/w300_and_h450_bestv2" . $infos->getPosterPath()
                        : '',
                ];
            }
        }

        return null;
    }
}
