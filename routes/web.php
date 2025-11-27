<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlShortenerController;
use App\Http\Controllers\GoogleAuthController;
Route::get('/', function () {
    return view('welcome');
});
Route::get('/google-auth', function () {
    return view('google-auth');
});
Route::get('/{short_code}', [UrlShortenerController::class, 'redirect']);
//DEVOLVER JSON
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

//DEVOLVER BLADE
Route::get('/auth/google/redirect-web', [GoogleAuthController::class, 'redirectToGoogle']); // ← DEBES USAR ESTA
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
Route::get('/profile', function () {
    return view('profile');
})->middleware('auth:sanctum');