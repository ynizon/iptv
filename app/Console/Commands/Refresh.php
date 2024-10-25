<?php

namespace App\Console\Commands;

use App\Models\Filter;
use App\Models\Playlist;
use App\Models\Url;
use App\Models\UrlBackup;
use App\Models\UrlError;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class Refresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh movies';

    /**
     * Execute the console command.
     */
    public function handle()
    {

//        $urls = Url::all();
//        foreach ($urls as $url) {
//            foreach (Filter::all() as $filter) {
//                if (stripos($url['category'], $filter->name) !== false) {
//                    $url->filter = 1;
//                    $url->save();
//                }
//            }
//        }
//
//        exit();
//        $urls = Url::all();
//        foreach ($urls as $url){
//            $movie = 1;
//            $tvChannel = 0;
//            $episod = 0;
//            $season = 0;
//            $serie = 0;
//
//            preg_match('/(\d{2}) E(\d{2})/', $url->name, $matches);
//            if (!empty($matches)) {
//                $serie = 1;
//                $movie = 0;
//                $season = $matches[1];
//                $episod = $matches[2];
//            }
//            if (stripos($url->category, ' TV ') !== false){
//                $tvChannel = 1;
//                $movie = 0;
//            }
//            $url->tvchannel = $tvChannel;
//            $url->movie = $movie;
//            $url->serie = $serie;
//            $url->season = $season;
//            $url->episod = $episod;
//            $url->save();
//        }
//
//        exit();

        $this->backupFavorites();
        foreach (Playlist::all() as $playlist) {
            if ($playlist->content == '') {
                $playlist = $this->downloadFile($playlist);
            }
            if ($playlist->content != '') {
                $this->parseFileAndCreateUrl($playlist);
            }
        }
        $this->restoreFavorites();
    }

    protected function backupFavorites()
    {
        Schema::drop('url_backups');
        $urls = Url::where("watched","=",1)->orWhere("favorite","=",1)->get();
        foreach ($urls as $url){
            $urlBackup = new UrlBackup();
            $urlBackup->url = $url->url;
            $urlBackup->favorite = $url->favorite;
            $urlBackup->watched = $url->watched;
            $urlBackup->save();
        }
    }

    protected function restoreFavorites()
    {
        $urlBackups = UrlBackup::all();
        foreach ($urlBackups as $urlBackup){
            $url = Url::where("url","=",$urlBackups->url)->first();
            $url->favorite = $urlBackup->favorite;
            $url->watched = $urlBackup->watched;
            $url->save();
        }
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
        $playlist->urls()->delete();

        foreach (explode("\n",$playlist->content) as $row) {
            if ($row != "#EXTM3U") {
                if (stripos($row, "#EXTINF:-1") !== false) {
                    preg_match('/tvg-id="([^"]*)" tvg-name="((?:[^"]|"")+)" tvg-logo="([^"]*)" group-title="([^"]*)"/', $row, $matches);
                    $url =  [];
                    if (isset($matches[4])) {
                        //                    $tvg_id = $matches[1];
                        $tvg_name = $matches[2];
                        $tvg_logo = $matches[3];
                        $group_title = $matches[4];
                        $serie = 0;
                        $movie = 1;
                        $tvChannel = 0;
                        $episod = 0;
                        $season = 0;

                        preg_match('/(\d{2}) E(\d{2})/', $tvg_name, $matches);
                        if (!empty($matches)) {
                            $serie = 1;
                            $movie = 0;
                            $season = $matches[1];
                            $episod = $matches[2];
                        }
                        if (stripos($group_title, ' TV ') !== false){
                            $tvChannel = 1;
                            $movie = 0;
                        }

                        $url =  [
                            'season' => $season,
                            'episod' => $episod,
                            'serie' => $serie,
                            'tvchannel' => $tvChannel,
                            'movie' => $movie,
                            'name' => $tvg_name,
                            'category' => $group_title,
                            'picture' => $tvg_logo,
                            'filter' => 0,
                            'watched' => 0,
                        ];
                    } else {
                        $urlerror = new UrlError();
                        $urlerror->url = $row;
                        $urlerror->playlist_id = $playlist->id;
                        $urlerror->save();
                    }
                } else {
                    if (isset($url['name']))
                    {
                        $url['url'] = trim($row);

                        foreach (Filter::all() as $filter)
                        {
                            if (stripos($url['category'], $filter->name) !== false){
                                $url['filter'] = 1;
                            }
                        }

                        $this->createUrl($url, $playlist);
                    }
                }
            }
        }
    }

    protected function createUrl($urlImport, $playlist)
    {
        $url = new Url();
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
}
