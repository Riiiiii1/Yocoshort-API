<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlShortenerController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\UrlUserController;

$mainApiHost = parse_url(config('app.url'), PHP_URL_HOST); 
$shortLinkDomain = config('app.short_link_domain', 'yocoshort.com');

Route::domain('{subdomain}.' . $shortLinkDomain)->group(function () {
    Route::get('/', function ($subdomain) {
        return redirect(config('app.frontend_url', 'https://www.yocoshort.com'));
    });
    Route::get('/{short_code}', [UrlUserController::class, 'handleRedirect']);
});

Route::domain($mainApiHost)->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });
    Route::prefix('auth/google')->group(function () {
        Route::get('/redirect-web', [GoogleAuthController::class, 'redirectToGoogle'])->name('login.google');
        Route::get('/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
    });
    Route::get('/profile', function () {
        return view('profile');
    })->middleware('auth:sanctum');
    Route::get('/{short_code}', [UrlShortenerController::class, 'redirect'])->name('url.redirect');
});