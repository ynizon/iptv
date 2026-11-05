<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisibleCategoryController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::id()){
        return redirect('dashboard');
    } else {
        //Automatically login
        $user = User::where("email","=",Cookie::get('email'))
            ->where("remember_token","=",Cookie::get('remember_token'))
            ->first();
        if ($user){
            Auth::loginUsingId($user->id);
            return redirect('dashboard');
        }

        $pictures = array_filter(glob(public_path('screenshots').'/*'), 'is_file');
        return view('welcome', compact('pictures'));
    }
});

Route::get('/tv/{token}', [SearchController::class, 'tv']);
// The stream links given to the players: the token stands for the user, the provider url stays hidden
Route::get('/s/{token}/{file}', [SearchController::class, 'stream'])->where('file', '[0-9]+(\.[A-Za-z0-9]+)?')->name('stream');
Route::get('/download/iptvreg', [SearchController::class, 'iptvreg']);
Route::get('/download/iptvsh', [SearchController::class, 'iptvsh']);
Route::get('/download/iptvdesktop', [SearchController::class, 'iptvdesktop']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('/users', UserController::class);
    Route::post('/users/{user}/tv-token', [UserController::class, 'tvToken'])->name('users.tv-token');
    Route::get('/settings', [SearchController::class, 'settings'])->name('settings');
    // Not named: one name per route (needed by route:cache)
    Route::post('/settings', [SearchController::class, 'settings']);
    Route::resource('/playlists', PlaylistController::class);
    Route::resource('/categories', CategoryController::class);
    Route::resource('/filters', FilterController::class);
    Route::get('/visible-categories', [VisibleCategoryController::class, 'index'])->name('visible-categories.index');
    Route::post('/visible-categories', [VisibleCategoryController::class, 'store'])->name('visible-categories.store');
    Route::get('/dashboard', [SearchController::class, 'dashboard'])->name('dashboard');
    Route::post('/search', [SearchController::class, 'search'])->name('search');
    Route::get('/favorite/{id}', [SearchController::class, 'favorite'])->name('favorite');
    Route::post('/favorite_serie', [SearchController::class, 'favorite_serie'])->name('favorite_serie');
    Route::get('/remove/{id}', [SearchController::class, 'remove'])->name('remove');
    Route::post('/remove_serie', [SearchController::class, 'remove_serie'])->name('remove_serie');
    Route::get('/forceWatched/{id}', [SearchController::class, 'forceWatched'])->name('forceWatched');
    Route::get('/watched/{id}', [SearchController::class, 'watched'])->name('watched');
    Route::get('/counter/{id}/{counter}', [SearchController::class, 'counter'])->name('counter');
    Route::get('/picture/{id}', [SearchController::class, 'picture'])->name('picture');
    Route::get('/detail/{id}', [SearchController::class, 'detail'])->name('detail');
    Route::get('/detail_serie/{id}', [SearchController::class, 'detailSerie'])->name('detail_serie');
    Route::get('/play/{id}', [SearchController::class, 'play'])->name('play');
    Route::post('/add_tv', [SearchController::class, 'add_tv'])->name('add_tv');
});

require __DIR__.'/auth.php';
