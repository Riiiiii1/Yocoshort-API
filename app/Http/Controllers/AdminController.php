<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserShortUrl;
class AdminController extends Controller
{
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
