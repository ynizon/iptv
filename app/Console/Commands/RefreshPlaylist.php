<?php

namespace App\Console\Commands;

use App\Helpers\Sqlite;
use App\Models\Filter;
use App\Models\Playlist;
use App\Models\Url;
use App\Models\UrlError;
use App\Models\UrlImport;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\DB;

class RefreshPlaylist extends Command
{

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
        UrlError::truncate();
        $sqlite->setWalJournalMode(
            $db = $sqlite->getDatabase($manager, 'sqlite')
        );

        $startTime = microtime(true);
        $nbPlaylist = 0;
        foreach (Playlist::all() as $playlist) {
            $nbPlaylist++;
            if ($playlist->content == '') {
                $this->info('Download Playlist Content #'.$nbPlaylist);
                $playlist = $this->downloadFile($playlist);
            }
            if ($playlist->content != '') {
                $this->info('Parse Playlist Urls #'.$nbPlaylist);
                $this->parseFileAndCreateUrl($playlist);
            }
        }

         DB::table('url_imports')->orderBy('id')->chunk(100, function ($rows) {
            $data = json_decode(json_encode($rows), true);
            DB::table('urls')->insertOrIgnore($data);
        });

        $this->info('Filter urls');
        $this->filterUrls();

        UrlImport::truncate();
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        $this->info("Finished : " . number_format($executionTime, 2) . " sec with ".UrlError::count() . " errors");
    }

    protected function downloadFile($playlist)
    {
        $timeout = 120;
        $ch = curl_init($playlist->url);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        if (preg_match('`^https://`i', $playlist->url))
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13');
        $content = curl_exec($ch);
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
            $nb++;
            $bar->advance();
            if ($row != "#EXTM3U") {
                if (stripos($row, "#EXTINF:-1") !== false) {
                    preg_match('/tvg-id="([^"]*)" tvg-name="((?:[^"]|"")+)" tvg-logo="([^"]*)" group-title="([^"]*)"/', $row, $matches);
                    $url =  [];
                    if (isset($matches[4])) {
                        //                    $tvg_id = $matches[1];
                        $tvg_name = trim($matches[2]);
                        $tvg_logo = trim($matches[3]);
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
                            'category' => $group_title,
                            'picture' => $tvg_logo,
                            'filter' => 0,
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
                            if ($nb >= 1000) {
                                UrlImport::insert($data);
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
            UrlImport::insert($data);
        }
        $this->info('');
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
        $url->tvchannel = $urlImport['tvchannel'];
        $url->serie = $urlImport['serie'];
        $url->movie = $urlImport['movie'];
        $url->playlist_id = $playlist->id;
        $url->save();
    }

    protected function filterUrls() {
        $bar = $this->output->createProgressBar(count($this->tvChannels) + count(Filter::all()));
        foreach (Filter::all() as $filter) {
            $bar->advance();
            Url::where("name", "like", "%" . $filter->name . "%")
                ->orWhere("category", "like", "%" . $filter->name . "%")
                ->update(["filter" => 1]);
        }

        foreach ($this->tvChannels as $tvChannel) {
            $bar->advance();
            Url::where("category", "like", "%" . $tvChannel . "%")
                ->orWhere("name", "like", "%" . $tvChannel . "%")
                ->update(["tvchannel" => 1 , "movie" =>0, "serie" =>0]);
        }

        $this->info('');
    }
}
