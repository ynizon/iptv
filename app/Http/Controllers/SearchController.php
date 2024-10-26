<?php

namespace App\Http\Controllers;

use App\Models\Url;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public const FILTERS = ["HD" => "", "SD" => "checked", "UHD" => "", "FHD" => ""];

    public function index(){

        $categories = [];
        $urls = DB::table('urls')->select('category')->where("filter","=",0)
            ->distinct()->orderBy("category")->get();
        foreach ($urls as $url)
        {
            $categories[] = $url->category;
        }

        return view('welcome', compact("categories"));
    }

    public function search(Request $request) {
        $query = $request->input("search");
        $formats = $request->input("formats");
        $category = $request->input("category");
        if ($formats == null) {$formats = [];}
        $urlsTmp = Url::where("name", "like", "%".$query."%")->where("filter","=",0);

        if ($category != '') {
            if ($category == -1) {
                $urlsTmp->where("favorite" , "=", 1);
            } else {
                $urlsTmp->where("category" , "=", $category);
            }
        }
        $urlsTmp = $urlsTmp->orderBy("name")->limit(3000)->get();
        $urls = [
                "movies" => [],
                "series" => [],
                "channels" => [],
            ];

        foreach ($urlsTmp as $url) {
            $ok = true;
            foreach ($formats as $format) {
                if (stripos($url->name, $format. " ") !== false) {
                    $ok = false;
                }
            }
            if ($ok) {
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

        return view("search", compact("urls"));
    }

    public function view(Request $request, $id) {
        $url = Url::findOrFail($id);

        shell_exec("c:\\videolan\\vlc\\vlc.exe $url->url");
        //https://codesamplez.com/programming/php-html5-video-streaming-tutorial
        return view("view");
    }

    public function favorite_serie(Request $request) {
        $name = $request->input("name");
        $urls = Url::where("serie","=","1")->where("name","like",$name."%")->get();
        foreach ($urls as $url){
            $url->favorite = !$url->favorite;
            $url->save();
        }
echo $name;
        echo count($urls);
        return view("view");
    }
    public function favorite(Request $request, $id) {
        $url = Url::findOrFail($id);
        $url->favorite = !$url->favorite;
        $url->save();
        return view("view");
    }

    public function watched(Request $request, $id) {
        $url = Url::findOrFail($id);
        $url->watched = !$url->watched;
        $url->save();
        return view("view");
    }
}
