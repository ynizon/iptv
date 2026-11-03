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
    protected $signature = 'refresh:info {--limit=0 : Number of urls to process, 0 for all} {--doubtful : Search again the movies whose TMDB year is far from the year of their name}';

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
        if (config('services.tmdb.key') == '') {
            $this->error('Set your TMDB_KEY api in your .env file.');
            exit();
        }
        DB::update("update urls set picture = '' where picture like '%365.tv%'");//Remove error picture

        $this->info('Refresh TMDB');
        $tmdb = Factory::create()->getTmdb(config('services.tmdb.key'));
        $search    = new Search($tmdb);

        $nb = 0;
        $query = Url::where("tvchannel", "!=", "1")->where("filter", 0);
        if ($this->option("doubtful")) {
            // Probably matched with another title (shown with a "TMDB data to check" warning)
            $query->where("year", ">", 0)->whereRaw("ABS((" . Url::NAME_YEAR_SQL . ") - year) > 1");
        } else {
            $query->where(function ($query) {
                // Series rated before their number of votes was stored
                $query->whereNull("imdb")->orWhere(fn ($q) => $q->where("serie", 1)->where("votes", "<=", 0)->where("note", ">", 0))
                    // Found before their length was stored
                    ->orWhereNull("duration");
            });
        }
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
                $year = $url->parsedName()["year"];
                $key = ($isMovie ? "movie:" : "tv:") . mb_strtolower($name) . ":" . $year;
                if (!array_key_exists($key, $found)) {
                    try {
                        $found[$key] = $this->findInfos($search, $tmdb, $isMovie, $name, $options, $year);
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
                $url->duration = $infos['duration'];
                if ($infos['poster'] != '' && $url->picture != $infos['poster']) {
                    $url->picture = $infos['poster'];
                    // Drop the resized copy of the previous picture
                    @unlink(public_path("images/playlist/{$url->id}/{$url->id}.jpg"));
                }
                $url->save();
            }
        });

        Url::computeScores();
        $this->call('refresh:cache');

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
     * Search TMDB with the full title, then with each side of "Original title : French title".
     * With the year of the name, the result released that year (or one year apart) is preferred:
     * the first result is often a namesake ("L'Avare" 1980 found as the 2022 one)
     * @return array|null votes, year, note, overview, poster
     */
    protected function findInfos(Search $search, $tmdb, bool $isMovie, string $name, array $options, ?int $year = null): ?array
    {
        $names = array_unique(array_merge([$name], array_map('trim', explode(" : ", $name))));
        $first = null;
        foreach ($names as $searchName) {
            if ($searchName == '') {
                continue;
            }
            $responses = iterator_to_array($this->searchShow($search, $isMovie, $searchName, $options), false);
            $first = $first ?? ($responses[0] ?? null);
            if ($year === null && $first !== null) {
                break;
            }
            $match = $this->sameYear($responses, $year);
            // Not in the most popular results: among the ones of that year
            if ($match === null && $year !== null) {
                $match = $this->sameYear(iterator_to_array($this->searchShow($search, $isMovie, $searchName, $options + ['year' => $year]), false), $year);
            }
            if ($match !== null) {
                return $this->infos($tmdb, $isMovie, $match->getId(), $options);
            }
        }

        // Nothing of that year: the most popular result (the page warns when the years differ)
        return $first !== null ? $this->infos($tmdb, $isMovie, $first->getId(), $options) : null;
    }

    protected function searchShow(Search $search, bool $isMovie, string $name, array $options): \Generator
    {
        return $isMovie ? $search->movie($name, $options) : $search->tvshow($name, $options);
    }

    /**
     * The first result released the given year, or one year apart (release dates differ between countries)
     */
    protected function sameYear(array $responses, ?int $year)
    {
        foreach ($responses as $response) {
            $released = (int) substr((string) $response->getReleaseDate(), 0, 4);
            if ($year !== null && $released > 0 && abs($released - $year) <= 1) {
                return $response;
            }
        }

        return null;
    }

    /**
     * @return array votes, year, note, overview, poster, duration (minutes, of an episode for a series)
     */
    protected function infos($tmdb, bool $isMovie, int $id, array $options): array
    {
        $item = new Item($tmdb);
        $infos = $isMovie ? $item->getMovie($id, $options) : $item->getTVShow($id, $options);

        return [
            'votes' => $infos->getNbNotes(),
            'duration' => $isMovie ? $infos->getRuntime() : $infos->getEpisodeRunTime(),
            'year' => $infos->getReleaseDate() != '' ? substr($infos->getReleaseDate(), 0, 4) : '',
            'note' => $infos->getNote(),
            'overview' => (string) $infos->getOverview(),
            'poster' => $infos->getPosterPath() != ''
                ? "https://image.tmdb.org/t/p/w300_and_h450_bestv2" . $infos->getPosterPath()
                : '',
        ];
    }
}
