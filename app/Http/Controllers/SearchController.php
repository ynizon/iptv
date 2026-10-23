<?php

namespace App\Http\Controllers;

use App\Models\Category;
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

class SearchController extends Controller
{
    public const FILTERS = ["HD", "SD", "UHD" , "FHD"];

    public function __construct(protected $replaceDns = [])
    {
        // Not the "content" column: it holds the whole M3U file
        $playlists = Playlist::select("tld", "ip")->get();
        foreach ($playlists as $playlist){
            if ($playlist->tld != '' && $playlist->ip != '') {
                $this->replaceDns[$playlist->tld] = $playlist->ip;
            }
        }
    }

    public function dashboard(){
        return view('dashboard');
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
                    $urlsTmp->whereIn("category", Category::groups()[$category] ?? [$category]);
                }
            }
        }
        // One limit per type: with a shared one, the episodes of the best rated series took every place,
        // and the channels (no rating) were always cut. Movies and episodes: best rated first
        $movies = (clone $urlsTmp)->where("movie", 1)->orderBy("score", "desc")->orderBy("name")->limit(1500)->get();
        $episodes = (clone $urlsTmp)->where("serie", 1)->orderBy("score", "desc")->orderBy("name")->limit(1500)->get();
        $channels = $urlsTmp->where("tvchannel", 1)->orderBy("name")->limit(3000)->get();
        $urlsTmp = $movies->concat($episodes)->concat($channels);
        $urls = [
                "movies" => [],
                "series" => [],
                "channels" => [],
            ];

        // First episode of each series: its picture, note and description stand for the series
        $firstEpisodes = [];
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
                        $firstEpisodes[$name] = $url;
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
                    // The url may have left the playlist since
                    'name' => Url::where("url", "=", $view->url)->value("name") ?? "?"];
            }
        }


        //Create m3u8 file in storage (if you use Kodi)
        $fp = fopen(storage_path(Auth::user()->id.".m3u8"),"w+");
        fputs($fp, "#EXTM3U".PHP_EOL);
        foreach ($urls["channels"] as $channel)
        {
            fputs($fp,'#EXTINF:-1 tvg-id="'.$channel['id'].'" tvg-name="'.$channel['name'].'" tvg-logo="'.$channel['picture'].'" group-title="'.$channel['category'].'",'.$channel['name']. PHP_EOL);
            fputs($fp,$channel['url'] . PHP_EOL);
        }
        foreach ($urls["movies"] as $movie)
        {
            fputs($fp,'#EXTINF:-1 tvg-id="'.$movie['id'].'" tvg-name="'.$movie['name'].'" tvg-logo="'.$movie['picture'].'" group-title="'.$movie['category'].'",'.$movie['name'].PHP_EOL);
            fputs($fp,$movie['url'] . PHP_EOL);
        }
        fclose($fp);

        return view("search", compact("urls", "firstEpisodes", "warnings"));
    }

    private function replaceUrlDns($url){
        foreach ($this->replaceDns as $tld => $ip) {
            $url = str_replace($tld, $ip, $url);
        }

        return $url;
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

        //Look for next episod
        if ($url->serie == 1) {
            $nextEpisod = $url = Url::where("short_name","=",$url->short_name)
                ->where("season","=",$url->season)
                ->where("episod","=",($url->episod+1))->first();

            if (!$nextEpisod){
                $nextEpisod = $url = Url::where("short_name","=",$url->short_name)
                    ->where("season","=",($url->season)+1)
                    ->where("episod","=",1)->first();
            }

            if ($nextEpisod){
                $viewNextEpisod = View::firstOrNew(
                    ['url' => $nextEpisod->url, 'user_id' => Auth::user()->id],
                    ['url' => $nextEpisod->url, 'user_id' => Auth::user()->id],
                );

                $viewNextEpisod->counter = 0;
                $viewNextEpisod->read_at = now();
                $viewNextEpisod->save();
            }
        }

        return view("view");
    }

    public function add_tv(Request $request)
    {
        $user = Auth::user();
        $request->validate(['url' => 'required|url']);
        $user->url_stream = $request->input("url");
        $user->save();
        return response()->json(['ok' => true]);
    }

    public function tv($token) {
        $user = User::where('tv_token', $token)->first();
        if (!$user || !$user->url_stream) {
            abort(404);
        }
        return redirect($user->url_stream);
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

    public function picture($id) {
        $default = public_path('images/default.webp');
        try {
            $file = public_path("images/playlist/$id/$id.jpg");
            $logo = public_path("images/playlist/$id/logo.jpg");
            if (file_exists($logo)) {
                return response()->file($logo);
            }
            if (file_exists($file)) {
                return response()->file($file);
            }

            $url = Url::findOrFail($id);
            $content = $url->picture != '' ? $this->downloadPicture(Url::normalizePicture($url->picture)) : false;
            if ($content === false) {
                // Not cached: the picture will be tried again next time.
                // A channel shows its name instead (the page handles the 404)
                return $url->tvchannel ? response("", 404) : response()->file($default);
            }

            if ($url->tvchannel && extension_loaded('gd')) {
                $this->saveLogoTile($content, $logo);
                return response()->file($logo);
            }

            if (extension_loaded('gd')) {
                $manager = new ImageManager(
                    new \Intervention\Image\Drivers\Gd\Driver()
                );
                $width = 120;
                $height = 180;
                if (!is_dir(dirname($file))) {
                    mkdir(dirname($file), 0755, true);
                }
                $image = $manager->read($content);
                $image->resize($width, $height);
                $image->toJpg()->save($file);
                return response()->file($file);
            } else {
                $ctype = (new \finfo(FILEINFO_MIME_TYPE))->buffer($content);
                return response($content)->header('Content-Type', $ctype);
            }
        } catch (\Throwable $e) {
            return response()->file($default);
        }
    }

    /**
     * A channel logo on a 16:9 tile, not stretched: transparent logos are mostly black or white,
     * so the tile is dark for a light logo and light for a dark one
     */
    private function saveLogoTile(string $content, string $file): void {
        $width = 320;
        $height = 180;
        $padding = 20;

        $source = imagecreatefromstring($content);
        if ($source === false) {
            throw new Exception("Not an image");
        }
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        // Average lightness of the visible pixels, on a small copy
        $sample = imagecreatetruecolor(32, 18);
        imagealphablending($sample, false);
        imagesavealpha($sample, true);
        imagefill($sample, 0, 0, imagecolorallocatealpha($sample, 0, 0, 0, 127));
        imagecopyresampled($sample, $source, 0, 0, 0, 0, 32, 18, $sourceWidth, $sourceHeight);
        $lightness = 0;
        $weight = 0;
        $transparent = 0;
        for ($x = 0; $x < 32; $x++) {
            for ($y = 0; $y < 18; $y++) {
                $color = imagecolorsforindex($sample, imagecolorat($sample, $x, $y));
                $opacity = (127 - $color['alpha']) / 127;
                if ($opacity < 0.5) {
                    $transparent++;
                }
                $lightness += $opacity * (0.2126 * $color['red'] + 0.7152 * $color['green'] + 0.0722 * $color['blue']) / 255;
                $weight += $opacity;
            }
        }
        $lightLogo = $weight > 0 && $lightness / $weight > 0.55;
        // An opaque logo brings its own background: keep the dark tile of the page
        $background = ($lightLogo || $transparent == 0) ? [0x1B, 0x20, 0x28] : [0xE8, 0xEA, 0xED];

        $tile = imagecreatetruecolor($width, $height);
        imagefill($tile, 0, 0, imagecolorallocate($tile, ...$background));
        imagealphablending($tile, true);
        $scale = min(($width - 2 * $padding) / $sourceWidth, ($height - 2 * $padding) / $sourceHeight);
        $logoWidth = (int) round($sourceWidth * $scale);
        $logoHeight = (int) round($sourceHeight * $scale);
        imagecopyresampled($tile, $source, intdiv($width - $logoWidth, 2), intdiv($height - $logoHeight, 2),
            0, 0, $logoWidth, $logoHeight, $sourceWidth, $sourceHeight);

        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }
        imagejpeg($tile, $file, 88);
    }

    /**
     * Download a picture, false when it is not reachable or not an image
     */
    private function downloadPicture(string $picture): string|false {
        $ch = curl_init($picture);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $content = curl_exec($ch);
        $returnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        // Deleted imgur pictures redirect to their "removed" picture
        $removed = str_starts_with(basename((string) parse_url((string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL), PHP_URL_PATH)), "removed.");
        curl_close($ch);

        if ($content === false || $content === '' || $returnCode != 200 || stripos($contentType, 'image/') !== 0 || $removed) {
            return false;
        }

        return $content;
    }
	
	public function check($id){		
		$url = Url::findOrFail($id);
		if ($url->url != '') {
			$ch = curl_init();
					$headers = [
				'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36',
				'Accept: */*',
				'Accept-Language: fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7',
				'Range: bytes=0-1024', // On demande juste le premier Ko du film
				'Connection: keep-alive',
			];
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			
			// 2. On accepte tout type de contenu
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Range: bytes=0-1', // On ne demande que le tout premier octet du film
				'Connection: close'
			));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Suit les redirections
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Récupère la réponse sans l'afficher
			curl_setopt($ch, CURLOPT_HEADER, false);           // Inclut les en-têtes dans la sortie
			curl_setopt($ch, CURLOPT_NOBODY, true);           // N'inclut que les en-têtes dans la réponse

			curl_setopt($ch, CURLOPT_URL, $url->url);
			$returnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			
			$result = curl_exec($ch);

			if ($result === false) {
				// Affiche l'erreur précise (ex: "Could not resolve host" ou "Connection refused")
				echo 'Erreur cURL : ' . curl_error($ch);
			} else {
				$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				echo "Code HTTP : " . $httpCode;
			}

			curl_close($ch);

			echo $returnCode;
		}
	}
}
