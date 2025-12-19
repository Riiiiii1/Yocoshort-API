<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Url;
use Illuminate\Support\Str;


class UrlShortenerController extends Controller
{
    /**
     * Summary of store
     * @param \Illuminate\Http\Request $request
     * @return void
     * Función Post que valida la ulr maxima de 500 caracteres, crea un 
     */
    public function store(Request $request){
        $request->validate([
            'long_url' => 'required|url|max:500'
        ]);

        $longUrl = rtrim($request->long_url, '/');

        $detect = Url::where('long_url', $longUrl)->first();

        if ($detect) {
            $detect->update(['expires_at' => now()->addDays(2)]);
            return response()->json([
                'short_url' => url($detect->short_code),
                'expires_at' => $detect->expires_at
            ]);
        }
        do {
            $unique_code = Str::random(7);
        } while (Url::where('short_code', $unique_code)->exists());
        $url = Url::create([
            'long_url' => $longUrl,
            'short_code' => $unique_code,
            'clicks' => 0,
            'expires_at' => now()->addDays(7)
        ]);
        return response()->json([
            'short_url' => url($url->short_code),
            'expires_at' => $url->expires_at
        ]);
    }


    public function redirect($short_code){
        $url = Url::where('short_code', $short_code)->first();
        
        if (!$url) {
            abort(404);
        }

        $url->increment('clicks');
        return redirect()->away($url->long_url);
    }
    /**
     * Summary of example
     * @return \Illuminate\Http\JsonResponse
     * Ejemplo de Como insertar directamente sin usar un Request.
     * Devuelve con la función url del backend, sumado con el objeto url-shortcode
     */
    public function example(){
        $url = Url::create([
            'long_url' => 'https://youtube.com',
            'short_code' => 'ansdak1',
            'clicks' => 0
        ]);
        return response()->json([
            'short_url' => url('/' . $url->short_code)
        ]);
    }
    /**
     * Función que se ejecuta al momento de ejecutarse el evento de creación de registro. En este caso URL::create 
     * Defines el pasametro en static::created(function ($url) cuando hay un estado created.
     * @return void
     */

    // Limpieza de URLs caducadas desde un cronjob
    public function cleanup(Request $request) {
        if ($request->query('key') !== config('app.cron_secret', 'ko1022123834mdsjqolzdm')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $deletedCount = Url::whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();

        return response()->json([
            'status' => 'alive',
            'message' => 'Expired URLs cleaned up successfully',
            'deleted_count' => $deletedCount,
            'timestamp' => now()
        ]);
    }
}
