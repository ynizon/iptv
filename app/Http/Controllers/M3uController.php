<?php

namespace App\Http\Controllers;

use App\Models\User;
use Intervention\Image\ImageManager;
use App\Models\Playlist;
use App\Models\Url;
use App\Models\View;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class M3uController extends Controller
{
    public const FILTERS = ["HD", "SD", "UHD" , "FHD"];

    public function __construct(protected $replaceDns = [])
    {
        $playlists = Playlist::all();
        foreach ($playlists as $playlist){
            if ($playlist->tld != '' && $playlist->ip != '') {
                $this->replaceDns[$playlist->tld] = $playlist->ip;
            }
        }
    }

    public function m3u(Request $request) {
        $query = $request->input("search");
        if ($query == null ){$query = "@@@";}
        $formats = $request->input("formats");
        $urlsOK = $request->input("urlsOK");
        if ($urlsOK == null) {
            $urlsOK = [];
        }
        $content = $request->input("content");
        $category = $request->input("category");
        if ($formats == null) {$formats = [];}
        $urlsTmp = Url::where("name", "like", "%".$query."%")->where("filter","=",0);

        $urlsTmp = $urlsTmp->orderBy("name")->limit(3000)->get();
        $urls = [
                "movies" => [],
                "series" => [],
                "channels" => [],
            ];

        $pictures = [];
        $descriptions = [];
        foreach ($urlsTmp as $url) {
            $ok = true;
            foreach ($formats as $format) {
                if (stripos($url->name, $format. " ") !== false) {
                    $ok = false;
                }
            }
            if ($ok) {
                $url->urlFinal = $this->replaceUrlDns($url->url);
                if ($url->movie) {
                    $urls["movies"][] = $url;
                }
                if ($url->serie) {
                    $name = trim($url->name);
                    $episod = 0;
                    $season = 0;
                    preg_match('/(\d{2}) E(\d{2})/', $name, $matches);
                    if (!empty($matches)) {
                        $season = $matches[1];
                        $episod = $matches[2];
                    }

                    preg_match('/^(.*?)(?=S\d{2} E\d{2})/', $name, $matches);
                    if (!empty($matches)) {
                        $name = trim($matches[1]);
                    }
                    if (!isset($urls["series"][$name]))  {
                        $urls["series"][$name] = [];
                        $pictures[$name] = $url->id;
                        $descriptions[$name] = $url->imdb;
                    }
                    if (!isset($urls["series"][$name][$season]))  {
                        $urls["series"][$name][$season] = [];
                    }

                    $urls["series"][$name][$season][$episod] = $url;
                }
                if ($url->tvchannel) {
                    $urls["channels"][] = $url;
                }
            }
        }

        $date = new DateTime();
        $date->modify('-2 hours');
        $time2Hour = $date->format('Y-m-d H:i:s');
        $views = View::where("user_id", "!=", Auth::user()->id)->where("read_at", ">=", $time2Hour)->orderBy('read_at','desc')->get();
        $warnings = [];
        foreach ($views as $view) {
            if (!isset($warnings[$view->user->id]) && $view->counter>0) {
                $warnings[$view->user->id] = ['user' => $view->user->name,
                    'from' => substr(substr($view->read_at, -8), 0, 5),
                    'counter' => Url::formatHour($view->counter),
                    'name' => Url::where("url", "=", $view->url)->first()->name];
            }
        }


        //Create m3u8 file in storage (if you use Kodi)
        $file = storage_path("test".Auth::user()->id.".m3u8");

        if ($content != ""){
            file_put_contents($file, $content);
        }

        if (count($urlsOK)>0) {
            if (!file_exists($file)) {
                $fp = fopen($file, "w+");
                fputs($fp, "#EXTM3U" . PHP_EOL);
            } else {
                $fp = fopen($file, "a+");
            }

            foreach ($urlsOK as $url_id) {
                $url = Url::find($url_id);
                fputs($fp, PHP_EOL.PHP_EOL.'#EXTINF:-1 tvg-id="' . $url['id'] . '" tvg-name="' . $url['name'] . '" tvg-logo="' . $url['picture'] . '" group-title="' . $url['category'] . '",' . $url['name'] . PHP_EOL);
                fputs($fp, $url['url'] . PHP_EOL);
                fputs($fp, PHP_EOL);
            }
            fclose($fp);
            return redirect("/m3u");
        }

        if (file_exists($file) && $content == null) {
            $content = file_get_contents($file);
        }
        return view("m3u", compact("urls", "content"));
    }

    private function replaceUrlDns($url){
        foreach ($this->replaceDns as $tld => $ip) {
            $url = str_replace($tld, $ip, $url);
        }

        return $url;
    }
}
