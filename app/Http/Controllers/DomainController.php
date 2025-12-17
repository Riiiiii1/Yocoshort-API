<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Domain;

class DomainController extends Controller
{
    protected $reserved = ['www', 'admin', 'api', 'dashboard', 'login', 'register', 'support'];

    public function storeOrUpdate(Request $request){
        $request->validate([
            'subdomain' => [
                'required',
                'string',
                'min:3',
                'max:20',
                'alpha_dash', 
                Rule::notIn($this->reserved),
                Rule::unique('domains', 'subdomain')->ignore($request->user()->id, 'user_id'),
            ],
        ]);

        $domain = Domain::updateOrCreate(
            ['user_id' => $request->user()->id], 
            [
                'subdomain' => strtolower($request->subdomain), 
            ]
        );

        return response()->json([
            'message' => 'Subdominio configurado exitosamente.',
            'domain' => $domain->subdomain . '.' . config('app.url_base', 'short.gy'), 
            'data' => $domain
        ], 200);
    }
    public function index(Request $request){
        $domain = Domain::where('user_id', $request->user()->id)->first();
        
        if (!$domain) {
            return response()->json(['message' => 'No domain found'], 404);
        }
        
        return response()->json($domain);
    } 
    public function destroy(Request $request){
        $domain = Domain::where('user_id', $request->user()->id)->first();
        if ($domain) {
            $domain->delete();
            return response()->json(['message' => 'Dominio eliminado']);
        }
        return response()->json(['message' => 'No tienes dominio'], 404);
    }
}
