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

}
