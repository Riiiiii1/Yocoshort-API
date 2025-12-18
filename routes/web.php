<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlShortenerController;
use App\Http\Controllers\GoogleAuthController;

// 1. RUTAS FIJAS (Tienen prioridad)
Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/google/redirect-web', [GoogleAuthController::class, 'redirectToGoogle'])->name('login.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

// 2. LA RUTA COMODÍN (Debe ser la ÚLTIMA de todas)
// Nota: Quitamos el grupo de dominio para que funcione en CUALQUIER subdominio que Render acepte
Route::get('/{short_code}', [UrlShortenerController::class, 'redirect'])->name('url.redirect');