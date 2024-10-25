<?php

use App\Http\Controllers\CronController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::resource('/playlists', PlaylistController::class);
Route::resource('/filters', FilterController::class);
Route::get('/',  [SearchController::class, 'index'])->name('welcome');
Route::post('/search',  [SearchController::class, 'search'])->name('search');
Route::get('/favorite/{id}',  [SearchController::class, 'favorite'])->name('favorite');
Route::post('/favorite_serie',  [SearchController::class, 'favorite_serie'])->name('favorite_serie');
Route::get('/watched/{id}',  [SearchController::class, 'watched'])->name('watched');
Route::get('/view/{id}',  [SearchController::class, 'view'])->name('view');
Route::get('/cron',  [CronController::class, 'cron'])->name('cron');
