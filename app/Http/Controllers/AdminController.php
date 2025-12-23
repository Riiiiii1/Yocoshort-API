<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserShortUrl;
class AdminController extends Controller
{   /**
     * @OA\Get(
     * path="/api/admin/stats",
     * summary="Obtener estadísticas generales y lista de usuarios",
     * tags={"Admin"},
     * @OA\Parameter(
     * name="page",
     * in="query",
     * description="Número de página para la paginación",
     * required=false,
     * @OA\Schema(type="integer", default=1)
     * ),
     * @OA\Parameter(
     * name="per_page",
     * in="query",
     * description="Cantidad de usuarios por página",
     * required=false,
     * @OA\Schema(type="integer", default=10)
     * ),
     * @OA\Response(
     * response=200,
     * description="Estadísticas obtenidas exitosamente",
     * @OA\JsonContent(
     * @OA\Property(property="total_users", type="integer", example=150),
     * @OA\Property(property="total_links", type="integer", example=5300),
     * @OA\Property(
     * property="users",
     * type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Juan Pérez"),
     * @OA\Property(property="email", type="string", format="email", example="juan@test.com"),
     * @OA\Property(property="created_at", type="string", format="date-time")
     * )
     * ),
     * @OA\Property(
     * property="pagination",
     * type="object",
     * @OA\Property(property="current_page", type="integer", example=1),
     * @OA\Property(property="last_page", type="integer", example=5),
     * @OA\Property(property="per_page", type="integer", example=10),
     * @OA\Property(property="total", type="integer", example=50)
     * )
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="No autorizado"
     * )
     * )
     */
    public function stats(Request $request){
        $total_users = User::count();
        $total_links = UserShortUrl::count();
        $per_page = $request->input('per_page', 10); 
        $page = $request->input('page', 1); 
        $users = User::select('id', 'name', 'email', 'created_at')
                    ->orderBy('created_at', 'desc')
                    ->paginate($per_page, ['*'], 'page', $page);

        return response()->json([
            'total_users' => $total_users,
            'total_links' => $total_links,
            'users' => $users->items(), 
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
                'next_page_url' => $users->nextPageUrl(),
                'prev_page_url' => $users->previousPageUrl(),
            ]
        ]);
    }
    /**
     * @OA\Get(
     * path="/api/admin/users/search",
     * summary="Buscar usuarios por nombre o email",
     * tags={"Admin"},
     * @OA\Parameter(
     * name="search",
     * in="query",
     * description="Término de búsqueda (nombre o email)",
     * required=false,
     * @OA\Schema(type="string", example="juan")
     * ),
     * @OA\Parameter(
     * name="per_page",
     * in="query",
     * description="Cantidad de resultados por página",
     * required=false,
     * @OA\Schema(type="integer", default=10)
     * ),
     * @OA\Response(
     * response=200,
     * description="Búsqueda exitosa",
     * @OA\JsonContent(
     * @OA\Property(
     * property="users",
     * type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="id", type="integer", example=10),
     * @OA\Property(property="name", type="string", example="Juanito"),
     * @OA\Property(property="email", type="string", example="juanito@mail.com"),
     * @OA\Property(property="created_at", type="string", format="date-time")
     * )
     * ),
     * @OA\Property(
     * property="pagination",
     * type="object",
     * @OA\Property(property="total", type="integer", example=1)
     * )
     * )
     * )
     * )
     */
    public function searchUsers(Request $request){
        $query = User::query();
        
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }
        
        $per_page = $request->input('per_page', 10);
        $users = $query->select('id', 'name', 'email', 'created_at')
                      ->orderBy('created_at', 'desc')
                      ->paginate($per_page);
        
        return response()->json([
            'users' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ]
        ]);
    }
}
