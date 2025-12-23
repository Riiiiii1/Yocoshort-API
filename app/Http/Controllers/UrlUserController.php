<?php

namespace App\Http\Controllers;
use Jenssegers\Agent\Agent;
use App\Models\UserShortUrl;
use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Click;
class UrlUserController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/links",
     * summary="Crear un nuevo enlace corto",
     * tags={"User Links"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="original_url", type="string", format="url", example="https://www.google.com"),
     * @OA\Property(property="etiquetas", type="string", example="Campaña Verano"),
     * @OA\Property(property="custom_alias", type="string", example="promo-2025")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Enlace creado exitosamente",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Link creado exitosamente."),
     * @OA\Property(property="short_url", type="string", example="http://marca.yocoshort.com/promo-2025"),
     * @OA\Property(property="data", type="object")
     * )
     * ),
     * @OA\Response(response=422, description="Error de validación o falta configurar dominio")
     * )
     */
    public function store(Request $request){
        $user = $request->user();
        $domain = Domain::where('user_id', $user->id)->first();

        if (!$domain) {
            return response()->json(['error' => 'Primero debes configurar tu subdominio (Marca).'], 422);
        }
        $request->validate([
            'original_url' => 'required|url',
            'etiquetas'    => 'nullable|string|max:255', 
            'custom_alias' => [ 
                'nullable',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('user_short_links', 'short_code')->where(function ($query) use ($domain) {
                    return $query->where('domain_id', $domain->id);
                }),
            ],
        ]);
        $shortCode = $request->custom_alias;
        if (!$shortCode) {
            do {
                $shortCode = Str::random(6);
            } while (UserShortUrl::where('domain_id', $domain->id)->where('short_code', $shortCode)->exists());
        }
        $shortUrl = UserShortUrl::create([
            'domain_id'    => $domain->id,
            'original_url' => $request->original_url,
            'short_code'   => $shortCode,    
            'etiquetas'    => $request->etiquetas,
            'clicks'       => 0,
        ]);
        // AQUI NO USO EL .ENV
        $fullUrl = 'http://' . $domain->subdomain . '.local.yocoshort.com/' . $shortCode;

        return response()->json([
            'message' => 'Link creado exitosamente.',
            'short_url' => $fullUrl,
            'data' => $shortUrl
        ], 201);
    }
    /**
     * @OA\Get(
     * path="/api/links",
     * summary="Listar todos mis enlaces",
     * tags={"User Links"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Lista de enlaces obtenida",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="short_code", type="string", example="promo-2025"),
     * @OA\Property(property="original_url", type="string", example="https://google.com"),
     * @OA\Property(property="clicks", type="integer", example=120)
     * )
     * )
     * )
     * )
     * )
     */
    public function index(Request $request){
        $domain = Domain::where('user_id', $request->user()->id)->first();
        
        if (!$domain) {
            return response()->json(['data' => []]);
        }

        $links = UserShortUrl::where('domain_id', $domain->id)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return response()->json(['data' => $links]);
    }
    /**
     * @OA\Put(
     * path="/api/links/{id}",
     * summary="Actualizar alias (short code)",
     * tags={"User Links"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, description="ID del enlace", @OA\Schema(type="integer")),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(@OA\Property(property="short_code", type="string", example="nuevo-alias"))
     * ),
     * @OA\Response(
     * response=200,
     * description="Actualizado correctamente",
     * @OA\JsonContent(@OA\Property(property="message", type="string", example="Alias actualizado"))
     * ),
     * @OA\Response(response=404, description="Link no encontrado")
     * )
     */
    public function update(Request $request, $id){
        $domain = Domain::where('user_id', $request->user()->id)->first();
        $link = UserShortUrl::where('domain_id', $domain->id)
                    ->where('id', $id)
                    ->first();

        if (!$link) return response()->json(['message' => 'Link no encontrado'], 404);

        $request->validate([
            'short_code' => [
                'required', 'string', 'max:50', 'alpha_dash',
                Rule::unique('user_short_links', 'short_code')
                    ->where('domain_id', $domain->id)
                    ->ignore($link->id)
            ],
        ]);

        $link->update(['short_code' => $request->short_code]);

        return response()->json(['message' => 'Alias actualizado', 'data' => $link]);
    }
    /**
     * @OA\Delete(
     * path="/api/links/{id}",
     * summary="Eliminar un enlace",
     * tags={"User Links"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, description="ID del enlace", @OA\Schema(type="integer")),
     * @OA\Response(
     * response=200,
     * description="Eliminado correctamente",
     * @OA\JsonContent(@OA\Property(property="message", type="string", example="Link eliminado"))
     * )
     * )
     */
    public function destroy(Request $request, $id){
        $domain = Domain::where('user_id', $request->user()->id)->first();
        $link = UserShortUrl::where('domain_id', $domain->id)
                    ->where('id', $id)
                    ->first();
        if (!$link) return response()->json(['message' => 'Link no encontrado'], 404);
        $link->delete();
        return response()->json(['message' => 'Link eliminado']);
    }
    public function handleRedirect($subdomain, $short_code, Request $request){
        $domain = Domain::where('subdomain', $subdomain)->firstOrFail();
        $link = UserShortUrl::where('domain_id', $domain->id)
                            ->where('short_code', $short_code)
                            ->firstOrFail();
        $this->recordClick($link, $request);
        return redirect()->away($link->original_url);
    }
    // Aux
    protected function recordClick($link, Request $request){
        $link->increment('clicks');
        $agent = new Agent();
        $agent->setUserAgent($request->header('User-Agent'));

        Click::create([
            'user_short_link_id' => $link->id,
            'clicked_at' => now(),
            'ip_address' => $request->ip(),
            'browser'    => $agent->browser(),
            'platform'   => $agent->platform(),
            'referer'    => $request->header('referer'), 
        ]);
    }

    /**
     * @OA\Get(
     * path="/api/links/{id}/metrics",
     * summary="Obtener métricas detalladas",
     * description="Devuelve total de clicks, desglose por navegadores y lista reciente.",
     * tags={"User Links"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, description="ID del enlace", @OA\Schema(type="integer")),
     * @OA\Response(
     * response=200,
     * description="Métricas procesadas",
     * @OA\JsonContent(
     * @OA\Property(property="total_clicks", type="integer", example=150),
     * @OA\Property(
     * property="browsers",
     * type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="browser", type="string", example="Chrome"),
     * @OA\Property(property="total", type="integer", example=45)
     * )
     * ),
     * @OA\Property(
     * property="recent",
     * type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="ip_address", type="string", example="192.168.1.1"),
     * @OA\Property(property="platform", type="string", example="Windows"),
     * @OA\Property(property="clicked_at", type="string", format="date-time")
     * )
     * )
     * )
     * ),
     * @OA\Response(response=404, description="Link no encontrado")
     * )
     */
    public function metrics(Request $request, $id){
        $domain = Domain::where('user_id', $request->user()->id)->first();
        $link = UserShortUrl::where('domain_id', $domain->id)
                            ->where('id', $id)
                            ->first();

        if (!$link) return response()->json(['message' => 'Link no encontrado'], 404);
        $history = Click::where('user_short_link_id', $link->id)
                        ->orderBy('clicked_at', 'desc')
                        ->get();
        $browsers = $history->groupBy('browser')->map(function ($group, $key) {
            return [
                'browser' => $key ?: 'Desconocido',
                'total' => $group->count()
            ];
        })->values(); 
        $recent = $history->take(20);
        return response()->json([
            'total_clicks' => $history->count(), 
            'browsers' => $browsers,
            'recent' => $recent
        ]);
    }

}
