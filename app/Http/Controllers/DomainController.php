<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Domain;

class DomainController extends Controller
{
    protected $reserved = ['www', 'admin', 'api', 'dashboard', 'login', 'register', 'support'];
    /**
     * @OA\Post(
     * path="/api/domain",
     * summary="Crear o actualizar mi subdominio",
     * tags={"Domains"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="subdomain", type="string", example="mi-marca", description="Min 3, Max 20 caracteres")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Dominio configurado",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Subdominio configurado exitosamente."),
     * @OA\Property(property="domain", type="string", example="mi-marca.yocoshort.com"),
     * @OA\Property(property="data", type="object")
     * )
     * ),
     * @OA\Response(response=422, description="Subdominio reservado, duplicado o inválido")
     * )
     */
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
    /**
     * @OA\Get(
     * path="/api/domain",
     * summary="Obtener configuración de mi dominio",
     * tags={"Domains"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Información del dominio",
     * @OA\JsonContent(
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="subdomain", type="string", example="mi-marca"),
     * @OA\Property(property="user_id", type="integer", example=10)
     * )
     * ),
     * @OA\Response(response=404, description="No tienes dominio configurado")
     * )
     */
    public function index(Request $request){
        $domain = Domain::where('user_id', $request->user()->id)->first();
        
        if (!$domain) {
            return response()->json(['message' => 'No domain found'], 404);
        }
        
        return response()->json($domain);
    } 
    /**
     * @OA\Delete(
     * path="/api/domain",
     * summary="Eliminar mi subdominio",
     * tags={"Domains"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Dominio eliminado",
     * @OA\JsonContent(@OA\Property(property="message", type="string", example="Dominio eliminado"))
     * ),
     * @OA\Response(response=404, description="No existe dominio para eliminar")
     * )
     */
    public function destroy(Request $request){
        $domain = Domain::where('user_id', $request->user()->id)->first();
        if ($domain) {
            $domain->delete();
            return response()->json(['message' => 'Dominio eliminado']);
        }
        return response()->json(['message' => 'No tienes dominio'], 404);
    }
}
