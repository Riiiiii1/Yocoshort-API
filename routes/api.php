<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlShortenerController;
use Resend\Laravel\Facades\Resend;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\UrlUserController;
use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use App\Http\Controllers\AdminController;

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

Route::post('/register', [AuthController::class, 'register']);
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) { return $request->user(); });
    Route::put('/user', [AuthController::class, 'updateProfile']);
    Route::delete('/user', [AuthController::class, 'destroy']);

    Route::delete('/domain', [DomainController::class, 'destroy']);
    Route::get('/domain', [DomainController::class, 'index']);
    Route::post('/domain', [DomainController::class, 'storeOrUpdate']);

    Route::get('/links', [UrlUserController::class, 'index']);
    Route::post('/links', [UrlUserController::class, 'store']);

    Route::put('/links/{id}', [UrlUserController::class, 'update']);
    Route::delete('/links/{id}', [UrlUserController::class, 'destroy']);
    
});
Route::get('/links/{id}/metrics', [UrlUserController::class, 'metrics'])->middleware('auth:sanctum');
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/stats', [AdminController::class, 'stats']);
    Route::get('/admin/users/search', [AdminController::class, 'searchUsers']); 
});


