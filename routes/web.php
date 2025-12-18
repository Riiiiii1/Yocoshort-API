<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlShortenerController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\UrlUserController;

/*
|--------------------------------------------------------------------------
| RUTAS DE SUBDOMINIOS (COMENTADAS POR LIMITACIÓN DE HOSTING)
|--------------------------------------------------------------------------
| Esta lógica está lista, pero requiere un hosting que soporte Wildcard DNS
| y certificados SSL dinámicos.
*/
/*
$shortLinkDomain = config('app.short_link_domain', 'yocoshort.com');

Route::domain('{subdomain}.' . $shortLinkDomain)->group(function () {
    Route::get('/', function ($subdomain) {
        return redirect(config('app.frontend_url', 'https://www.yocoshort.com'));
    });
    Route::get('/{short_code}', [UrlUserController::class, 'handleRedirect']);
});
*/


// Página de inicio
Route::get('/', function () {
    return view('welcome');
});

// Autenticación con Google
Route::prefix('auth/google')->group(function () {
    Route::get('/redirect-web', [GoogleAuthController::class, 'redirectToGoogle'])->name('login.google');
    Route::get('/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
});

// Perfil de Usuario (Protegido)
Route::get('/profile', function () {
    return view('profile');
})->middleware('auth:sanctum');

// Redireccionamiento simple
Route::get('/{short_code}', [UrlShortenerController::class, 'redirect'])->name('url.redirect');