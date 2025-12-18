<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlShortenerController;
use App\Http\Controllers\GoogleAuthController;
use App\Models\Domain;
use App\Models\UserShortUrl;
use App\Http\Controllers\UrlUserController;
use Illuminate\Support\Facades\Artisan;
Route::domain('{subdomain}.local.yocoshort.com')->group(function () {
    Route::get('/', function ($subdomain) {
        return redirect('http://local.yocoshort.com');
    });
    Route::get('/{short_code}', function ($subdomain, $short_code) {
        $domainModel = Domain::where('subdomain', $subdomain)->first();
        if (!$domainModel) {
            abort(404, 'Subdominio no encontrado');
        }
        $link = UserShortUrl::where('domain_id', $domainModel->id)
            ->where('short_code', $short_code)
            ->first();

        if (!$link) {
            abort(404, 'Link no encontrado');
        }
        $link->increment('clicks');
        return redirect()->away($link->original_url);
    });
});

Route::get('/init-db-yocoshort', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return "Migraciones ejecutadas con éxito: " . Artisan::output();
    } catch (\Exception $e) {
        return "Error al migrar: " . $e->getMessage();
    }
});
Route::domain('local.yocoshort.com')->group(function () {

    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/google-auth', function () {
        return view('google-auth');
    });

    Route::get('/auth/google/redirect-web', [GoogleAuthController::class, 'redirectToGoogle'])->name('login.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

    Route::get('/profile', function () {
        return view('profile');
    })->middleware('auth:sanctum');
    Route::get('/{short_code}', [UrlShortenerController::class, 'redirect']); 

});

Route::domain('{subdomain}.local.yocoshort.com')->group(function () {
    Route::get('/{short_code}', [UrlUserController::class, 'handleRedirect']);
});