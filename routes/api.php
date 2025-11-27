<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlShortenerController;
use Resend\Laravel\Facades\Resend;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('/example', [UrlShortenerController::class, 'example']);
Route::post('/long-url', [
    UrlShortenerController::class,
    'store'
]); //->middleware('throttle:5,1');
Route::post('/test-fast', function () {
    return response()->json(["ok" => true]);
});

Route::get('/test-mail', function () {
    Mail::raw('Funciona Resend!', function ($message) {
        $message->to('sisabuestandavidesteban@gmail.com')->subject('Prueba Resend');
    });
    return 'Correo enviado';
});

/*
* Autenticación y registro por correo usando Resend
*/
Route::post('/register', [AuthController::class, 'register']);
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail']);
