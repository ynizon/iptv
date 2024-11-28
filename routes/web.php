<?php

use App\Http\Controllers\CronController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/cron', [CronController::class, 'cron'])->name('cron');
Route::get('/', function () {
    if (Auth::id()){
        return redirect('dashboard');
    } else {
        $pictures = array_filter(glob(public_path('screenshots').'/*'), 'is_file');
        return view('welcome', compact('pictures'));
    }
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/download/iptvreg', [SearchController::class, 'iptvreg']);
Route::get('/download/iptvsh', [SearchController::class, 'iptvsh']);
Route::get('/download/iptvdesktop', [SearchController::class, 'iptvdesktop']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('/users', UserController::class);
    Route::get('/settings', [SearchController::class, 'settings'])->name('settings');
    Route::post('/settings', [SearchController::class, 'settings'])->name('settings');
    Route::resource('/playlists', PlaylistController::class);
    Route::resource('/filters', FilterController::class);
    Route::get('/dashboard', [SearchController::class, 'dashboard'])->name('dashboard');
    Route::post('/search', [SearchController::class, 'search'])->name('search');
    Route::get('/favorite/{id}', [SearchController::class, 'favorite'])->name('favorite');
    Route::post('/favorite_serie', [SearchController::class, 'favorite_serie'])->name('favorite_serie');
    Route::get('/remove/{id}', [SearchController::class, 'remove'])->name('remove');
    Route::post('/remove_serie', [SearchController::class, 'remove_serie'])->name('remove_serie');
    Route::get('/forceWatched/{id}', [SearchController::class, 'forceWatched'])->name('forceWatched');
    Route::get('/watched/{id}', [SearchController::class, 'watched'])->name('watched');
    Route::get('/counter/{id}/{counter}', [SearchController::class, 'counter'])->name('counter');
    Route::get('/view/{id}', [SearchController::class, 'view'])->name('view');
    Route::get('/picture/{id}', [SearchController::class, 'picture'])->name('picture');
});

require __DIR__.'/auth.php';
