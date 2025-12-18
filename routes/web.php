<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlShortenerController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\UrlUserController;

// 1. OBTENER DOMINIOS DESDE CONFIG
$mainApiHost = parse_url(config('app.url'), PHP_URL_HOST); // Ej: yocoshort.com o api.yocoshort.com
$shortLinkDomain = config('app.short_link_domain', 'yocoshort.com');

// 2. GRUPO DE SUBDOMINIOS (Ej: rara.yocoshort.com/ABC)
Route::domain('{subdomain}.' . $shortLinkDomain)->group(function () {
    Route::get('/', function ($subdomain) {
        return redirect(config('app.frontend_url', 'https://www.yocoshort.com'));
    });
    
    // Redirección para URLs creadas por usuarios en sus subdominios
    Route::get('/{short_code}', [UrlUserController::class, 'handleRedirect']);
});

// 3. GRUPO DEL DOMINIO PRINCIPAL (Ej: yocoshort.com o api.yocoshort.com)
Route::domain($mainApiHost)->group(function () {
    
    Route::get('/', function () {
        return view('welcome');
    });

    // Rutas de Autenticación
    Route::get('/auth/google/redirect-web', [GoogleAuthController::class, 'redirectToGoogle'])->name('login.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

    Route::get('/profile', function () {
        return view('profile');
    })->middleware('auth:sanctum');

    // REDIRECCIÓN NORMAL (Ej: yocoshort.com/XYZ)
    // Esta ruta debe estar DENTRO de este grupo y al FINAL de las rutas del grupo
    Route::get('/{short_code}', [UrlShortenerController::class, 'redirect'])->name('url.redirect');
});

// IMPORTANTE: Elimina cualquier Route::get('/{short_code}') que tengas fuera de los grupos.