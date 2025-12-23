<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Url;
use Illuminate\Support\Str;


class UrlShortenerController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/long-url",
     * summary="Acortar enlace publico sin loggeo.",
     * description="Crea un enlace corto temporal sin necesidad de registro.",
     * tags={"Public Shortener"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="long_url", type="string", format="url", example="https://youtube.com")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Enlace creado o renovado",
     * @OA\JsonContent(
     * @OA\Property(property="short_url", type="string", example="http://short.gy/AbCd12"),
     * @OA\Property(property="expires_at", type="string", format="date-time")
     * )
     * ),
     * @OA\Response(response=422, description="URL inválida o muy larga")
     * )
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
    /**
     * @OA\Get(
     * path="/{short_code}",
     * summary="Redireccionar enlace público",
     * tags={"Public Shortener"},
     * @OA\Parameter(
     * name="short_code",
     * in="path",
     * required=true,
     * description="Código del enlace corto",
     * @OA\Schema(type="string")
     * ),
     * @OA\Response(
     * response=302,
     * description="Redirección a la URL original"
     * ),
     * @OA\Response(response=404, description="Enlace no encontrado o expirado")
     * )
     */
    public function redirect($short_code){
        $url = Url::where('short_code', $short_code)->first();
        
        if (!$url) {
            abort(404);
        }

        $url->increment('clicks');
        return redirect()->away($url->long_url);
    }

    /**
     * @OA\Get(
     * path="/api/example",
     * summary="Ejemplo de creación manual",
     * tags={"Public Shortener"},
     * @OA\Response(
     * response=200,
     * description="Ejemplo ejecutado",
     * @OA\JsonContent(@OA\Property(property="short_url", type="string"))
     * )
     * )
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
     * @OA\Delete(
     * path="/api/cleanup",
     * summary="Limpiar enlaces expirados (Cronjob)",
     * tags={"System"},
     * @OA\Parameter(
     * name="key",
     * in="query",
     * required=true,
     * description="Clave secreta del Cronjob",
     * @OA\Schema(type="string")
     * ),
     * @OA\Response(
     * response=200,
     * description="Limpieza exitosa",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="string", example="alive"),
     * @OA\Property(property="deleted_count", type="integer", example=5)
     * )
     * ),
     * @OA\Response(response=401, description="Unauthorized (Clave incorrecta)")
     * )
     */
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
