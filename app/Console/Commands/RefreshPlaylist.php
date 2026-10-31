<?php

namespace App\Console\Commands;

use App\Helpers\Sqlite;
use App\Models\Playlist;
use App\Models\Url;
use App\Models\UrlError;
use App\Models\UrlImport;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RefreshPlaylist extends Command
{

    // Date of the urls found by this refresh, kept by the ones already known: null on the
    // first import, otherwise the whole catalog would be shown as new
    protected ?string $addedAt = null;

    protected array $tvChannels = [
        " TV ",
        "FR SPORTS (France)",
        "DAZN",
        "CANAL+",
        ".SPORTS",
        "-SPORTS",
        "EUROSPORT",
        "NBA PASS",
        "CHAINES.",
        "CHANNELS",
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh:playlist';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh movies';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseManager $manager, Sqlite $sqlite)
    {
		ini_set('memory_limit', '512M');
        $this->addedAt = Url::exists() ? date("Y-m-d H:i:s") : null;
        UrlError::truncate();
        $sqlite->setWalJournalMode(
            $db = $sqlite->getDatabase($manager, 'sqlite')
        );

        $startTime = microtime(true);

        $nbPlaylist = 0;
        // Playlists downloaded and parsed: only their urls can be removed
        $imported = [];
        foreach (Playlist::all() as $playlist) {
            $nbPlaylist++;
            $this->info('Download Playlist Content #'.$nbPlaylist);
            $playlist = $this->downloadFile($playlist);

            if ($playlist->content != '') {
                $this->info('Parse Playlist Urls #'.$nbPlaylist);
                $this->parseFileAndCreateUrl($playlist);
                $imported[] = $playlist;
            }
            $playlist->updated_at = date("Y-m-d H:i:s");
            $playlist->save();
        }

        // Before the comparison with the known urls, which went through it too
        $this->markTvChannels('url_imports');
        $this->info('');
        DB::table('url_imports')->orderBy('id')->chunk(100, function ($rows) {
            $data = $rows->map(function ($row) {
				$record = (array) $row;
				unset($record['id']);
				return $record;
			})->all();
            DB::table('urls')->insertOrIgnore($data);
        });

        $this->info('Update moved urls');
        $this->updateMovedUrls();
        foreach ($imported as $playlist) {
            $this->removeMissingUrls($playlist);
        }

        $this->info('Filter urls');
        $this->filterUrls();

        UrlImport::truncate();
        $this->call('refresh:cache');
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        $this->info("Finished : " . number_format($executionTime, 2) . " sec with ".UrlError::count() . " errors");
    }

    protected function replaceDomainWithIp($url, $ip) {
        $parts = parse_url($url);

        $parts['host'] = $ip;

        $scheme   = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host     = $parts['host'];
        $port     = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path     = isset($parts['path']) ? $parts['path'] : '';
        $query    = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return "$scheme$host$port$path$query$fragment";
    }

    protected function downloadFile($playlist)
    {

        $url = $playlist->url;
        if ($playlist->tld === '*'){
            $url = $this->replaceDomainWithIp($url, $playlist->ip);
        } else {
            if ($playlist->tld != '' && $playlist->ip != '') {
                $url = str_replace($playlist->tld, $playlist->ip, $url);
            }
        }

        $timeout = 120;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        if (preg_match('`^https://`i', $url))
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13');
        $content = curl_exec($ch);
        if($content === false)
        {
            $this->error( 'Error Curl : ' . curl_error($ch));
        }
        curl_close($ch);

        $playlist->content = $content;
        $playlist->save();
        return $playlist;
    }

    protected function parseFileAndCreateUrl($playlist)
    {
        $playlist->urlImports()->delete();

        $nb = 0;
        $urls = [];
        $data = [];
        $bar = $this->output->createProgressBar(count(explode("\n",$playlist->content)));

        foreach (explode("\n",$playlist->content) as $row) {
            $bar->advance();
            if ($row != "#EXTM3U") {
                if (stripos($row, "#EXTINF:-1") !== false) {
					$nb++;

                    preg_match('/tvg-id="([^"]*)" tvg-name="((?:[^"]|"")+)" tvg-logo="([^"]*)" group-title="([^"]*)"/', $row, $matches);
                    $url =  [];
                    if (isset($matches[4])) {
                        //                    $tvg_id = $matches[1];
                        $tvg_name = trim($matches[2]);
                        $tvg_logo = Url::normalizePicture(trim($matches[3]));
                        $group_title = trim($matches[4]);
                        $serie = 0;
                        $movie = 1;
                        $tvChannel = 0;
                        $episod = 0;
                        $season = 0;

                        $tvg_name_without_number = $tvg_name;
                        preg_match('/(\d{2}) E(\d{2})/', $tvg_name, $matches);
                        if (!empty($matches)) {
                            $serie = 1;
                            $movie = 0;
                            $season = trim($matches[1]);
                            $episod = trim($matches[2]);
                            $tvg_name_without_number = trim(str_replace("S".$season. " E".$episod, "", $tvg_name));
                        }

                        if (stripos($tvg_name_without_number, $group_title) !== false && $serie == 1){
                            $group_title = "Serie";
                        }

                        foreach ($this->tvChannels as $tvChannelTmp){
                            if (stripos($group_title, $tvChannelTmp) !== false){
                                $tvChannel = 1;
                                $movie = 0;
                                break;
                            }
                        }

                        $url =  [
                            'playlist_id' => $playlist->id,
                            'season' => $season,
                            'episod' => $episod,
                            'serie' => $serie,
                            'tvchannel' => $tvChannel,
                            'movie' => $movie,
                            'name' => $tvg_name,
                            'short_name' => $tvg_name_without_number,
                            'category' => $group_title,
                            'picture' => $tvg_logo,
                            'filter' => 0,
                            'created_at' => $this->addedAt,
                        ];
                    } else {
                        if (stripos($row, "#EXTINF:-1") === false) {
                            $urlerror = new UrlError();
                            $urlerror->url = $row;
                            $urlerror->playlist_id = $playlist->id;
                            $urlerror->save();
                        }
                    }
                } else {
                    if (isset($url['name']))
                    {
                        $url['url'] = trim($row);

                        if (!isset($urls[$url['url']])) {
                            $data[] = $url;
                            if ($nb >= 10000) {
                                $this->insertImports($data);
                                $data = [];
                                $nb = 0;
                                //$this->createUrl($url, $playlist);
                            }
                            $urls[$url['url']] = 1;
                        }
                    }
                }
            }
        }

        if (count($data) > 0){
            $this->insertImports($data);
        }
        $this->info('');
    }

    /**
     * By 1000 rows: SQLite limits the number of values of a query (32766 by default)
     */
    protected function insertImports(array $data)
    {
        foreach (array_chunk($data, 1000) as $rows) {
            UrlImport::insert($rows);
        }
    }

    protected function createUrl($urlImport, $playlist)
    {
        $url = new UrlImport();
        $url->url = $urlImport['url'];
        $url->episod = $urlImport['episod'];
        $url->season = $urlImport['season'];
        $url->category = $urlImport['category'];
        $url->filter = $urlImport['filter'];
        $url->picture = $urlImport['picture'];
        $url->name = $urlImport['name'];
        $url->short_name = $urlImport['short_name'];
        $url->tvchannel = $urlImport['tvchannel'];
        $url->serie = $urlImport['serie'];
        $url->movie = $urlImport['movie'];
        $url->playlist_id = $playlist->id;
        $url->save();
    }

    /**
     * Urls already known take the category, name and type of the new import: the provider moves
     * its titles (out of "FILMS RÉCEMMENT AJOUTÉS" for instance). The picture and the TMDB data are kept
     */
    protected function updateMovedUrls()
    {
        $columns = ['category', 'name', 'short_name', 'serie', 'movie', 'tvchannel', 'season', 'episod', 'playlist_id'];
        $set = implode(", ", array_map(fn ($column) => "$column = i.$column", $columns));
        $changed = implode(" OR ", array_map(fn ($column) => "urls.$column IS NOT i.$column", $columns));
        $nb = DB::update("UPDATE urls SET $set FROM url_imports i WHERE i.url = urls.url AND ($changed)");
        $this->info($nb . ' urls updated');
    }

    /**
     * Remove the urls of a playlist which are not in its new import anymore, with their pictures.
     * The views stay: favorites and history come back if the url comes back
     */
    protected function removeMissingUrls($playlist)
    {
        $missing = DB::table('urls')->where('playlist_id', $playlist->id)
            ->whereNotExists(fn ($query) => $query->select(DB::raw(1))->from('url_imports')->whereColumn('url_imports.url', 'urls.url'))
            ->pluck('id');
        $total = DB::table('urls')->where('playlist_id', $playlist->id)->count();
        // A cut download would empty the catalog: then nothing is removed
        if ($missing->isEmpty() || $missing->count() > $total / 2) {
            if ($missing->isNotEmpty()) {
                $this->error($missing->count() . ' of ' . $total . ' urls missing from playlist #' . $playlist->id . ': not removed, the download may be incomplete');
            }
            return;
        }

        foreach ($missing->chunk(500) as $ids) {
            DB::table('urls')->whereIn('id', $ids)->delete();
            foreach ($ids as $id) {
                File::deleteDirectory(public_path("images/playlist/$id"));
            }
        }
        $this->info($missing->count() . ' urls removed from playlist #' . $playlist->id);
    }

    protected function filterUrls() {
        $this->markTvChannels('urls');
        Url::applyFilters();
        $this->info('');
    }

    /**
     * A category or a name like a tv channel makes a channel
     */
    protected function markTvChannels(string $table) {
        $bar = $this->output->createProgressBar(count($this->tvChannels));
        foreach ($this->tvChannels as $tvChannel) {
            $bar->advance();
            DB::table($table)->where("category", "like", "%" . $tvChannel . "%")
                ->orWhere("name", "like", "%" . $tvChannel . "%")
                ->update(["tvchannel" => 1 , "movie" =>0, "serie" =>0]);
        }
    }
}
