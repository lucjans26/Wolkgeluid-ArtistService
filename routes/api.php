<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\ArtistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Artist
Route::get('/artists', [ArtistController::class, 'getArtist']);
Route::put('/artists', [ArtistController::class, 'updateArtist']);
Route::delete('/artists', [ArtistController::class, 'deleteArtist']);
Route::post('/artists', [ArtistController::class, 'createArtist']);

//Album
Route::get('/albums', [AlbumController::class, 'getAlbum']);
Route::put('/albums', [AlbumController::class, 'updateAlbum']);
Route::delete('/albums', [AlbumController::class, 'deleteAlbum']);
Route::post('/albums', [AlbumController::class, 'createAlbum']);
