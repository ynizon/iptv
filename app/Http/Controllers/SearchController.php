<?php

namespace App\Http\Controllers;

use App\Models\Url;
use App\Models\View;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public const FILTERS = ["HD", "SD", "UHD" , "FHD"];

    public function dashboard(){
        $categories = [];
        $urls = DB::table('urls')->select('category')->where("filter","=",0)
            ->distinct()->orderBy("category")->get();
        foreach ($urls as $url)
        {
            $categories[] = $url->category;
        }

        return view('dashboard', compact("categories"));
    }

    public function settings(Request $request) {
        if ($request->input('formats') == ''){
            return view('settings');
        } else {
            $user = Auth::user();
            $user->formats = json_encode($request->input('formats'));
            $user->save();

            return redirect()->route('settings')
                ->with('success', __('Formats updated successfully.'));
        }
    }

    public function search(Request $request) {
        $query = $request->input("search");
        $formats = $request->input("formats");
        $category = $request->input("category");
        if ($formats == null) {$formats = [];}
        $urlsTmp = Url::where("name", "like", "%".$query."%")->where("filter","=",0);

        if ($category != '') {
            //Favorites
            if ($category == -1) {
                $urlsTmp->leftJoin('views', 'urls.url', '=', 'views.url')
                ->where("favorite" , "=", 1)->where("views.user_id","=",Auth::user()->id)
                ->select("urls.*");
            } else {
                //Recent
                if ($category == -2) {
                    $urlsTmp->leftJoin('views', 'urls.url', '=', 'views.url')
                        ->where("views.user_id","=",Auth::user()->id)
                        ->select("urls.*")
                        ->orderBy("read_at","desc");
                } else {
                    //Category
                    $urlsTmp->where("category" , "=", $category);
                }
            }
        }
        $urlsTmp = $urlsTmp->orderBy("name")->limit(3000)->get();
        $urls = [
                "movies" => [],
                "series" => [],
                "channels" => [],
            ];

        $pictures = [];
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
                        $pictures[$name] = $url->picture;
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
        $views = View::where("user_id", "!=", Auth::user()->id)->where("read_at", ">=", $time2Hour)->get();
        $warnings = [];
        foreach ($views as $view) {
            $warnings[] = ['user' => $view->user->name,
                'from' => substr(substr($view->read_at,-8),0,5),
                'counter' => Url::formatHour($view->counter),
                'name' => Url::where("url","=",$view->url)->first()->name];
        }

        return view("search", compact("urls", "pictures", "warnings"));
    }

    public function view(Request $request, $id) {
        $url = Url::findOrFail($id);

        shell_exec("c:\\videolan\\vlc\\vlc.exe $url->url");
        return view("view");
    }

    public function favorite_serie(Request $request) {
        $name = $request->input("name");
        $urls = Url::where("serie","=","1")->where("name","like",$name."%")->get();
        foreach ($urls as $url){
            $view = View::firstOrNew(
                ['url' =>  $url->url, 'user_id' => Auth::user()->id],
                ['url' =>  $url->url, 'user_id' => Auth::user()->id],
            );
            $view->favorite = !$view->favorite;
            $view->save();
        }

        return view("view");
    }

    public function favorite(Request $request, $id) {
        $url = Url::findOrFail($id);
        $view = View::firstOrNew(
            ['url' =>  $url->url, 'user_id' => Auth::user()->id],
            ['url' =>  $url->url, 'user_id' => Auth::user()->id],
        );
        $view->favorite = !$view->favorite;
        $view->save();
        return view("view");
    }

    public function watched(Request $request, $id) {
        $url = Url::findOrFail($id);
        $view = View::firstOrNew(
            ['url' =>  $url->url, 'user_id' => Auth::user()->id],
            ['url' =>  $url->url, 'user_id' => Auth::user()->id],
        );

        $view->watched = !$view->watched;
        $view->save();
        return view("view");
    }

    public function remove(Request $request, $id) {
        $url = Url::findOrFail($id);
        View::where("user_id",Auth::user()->id)->where("url",$url->url)->delete();
    }

    public function remove_serie(Request $request) {
        $name = $request->input("name");
        $urls = Url::where("serie","=","1")->where("name","like",$name."%")->get();
        foreach ($urls as $url){
            View::where("user_id",Auth::user()->id)->where("url",$url->url)->delete();
        }
    }

    public function forceWatched(Request $request, $id) {
        $url = Url::findOrFail($id);
        $view = View::firstOrNew(
            ['url' =>  $url->url, 'user_id' => Auth::user()->id],
            ['url' =>  $url->url, 'user_id' => Auth::user()->id],
        );

        $view->watched = 1;
        $view->counter = 0;
        $view->save();
        return view("view");
    }

    public function counter(Request $request, $id, $counter) {

        $url = Url::findOrFail($id);
        $view = View::firstOrNew(
            ['url' => $url->url, 'user_id' => Auth::user()->id],
            ['url' => $url->url, 'user_id' => Auth::user()->id],
        );

        $view->counter = $counter;
        $view->read_at = now();
        $view->save();

        return view("view");
    }

    public function iptvreg() {
        $filePath = public_path('iptv/iptv.reg');

        return response()->download($filePath, 'iptv.reg', [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    public function iptvsh() {
        $filePath = public_path('iptv/iptv_vlc.sh');

        return response()->download($filePath, 'iptv_vlc.sh', [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    public function iptvdesktop() {
        $filePath = public_path('iptv/iptv.desktop');

        return response()->download($filePath, 'iptv.desktop', [
            'Content-Type' => 'application/octet-stream',
        ]);
    }
}
