<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect; 
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse{
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6'
            ]);

            $verificationToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $verificationToken);

            $newUser = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verification_token' => $tokenHash,
            ]);

            $verificationUrl = url("/api/verify-email/{$verificationToken}");

            Mail::to($newUser->email)->send(new VerifyEmail($newUser, $verificationUrl));

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente. Verifica tu email.',
                'data' => ['user' => $newUser]
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function verifyEmail($token){
        try {
            $tokenHash = hash('sha256', $token);
            
            $user = User::where('email_verification_token', $tokenHash)
                ->whereNull('email_verified_at')
                ->first();
                
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

            if (!$user) {
                return Redirect::to("{$frontendUrl}/login?error=invalid_token");
            }

            $user->email_verified_at = now();
            $user->email_verification_token = null;
            $user->save();
            
            $accessToken = $user->createToken('auth-token')->plainTextToken;
            $targetPath = ($user->role === 'admin') ? '/admin' : '/dashboard';

            return Redirect::to("{$frontendUrl}{$targetPath}?verified=success&token={$accessToken}");

        } catch (\Exception $e) {
             $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
             return Redirect::to("{$frontendUrl}/login?error=server_error");
        }
    }
    public function login(Request $request): JsonResponse{
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas.',
            ], 401);
        }

        $user = Auth::user();

        if ($user->email_verified_at === null) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor verifica tu correo antes de ingresar.',
            ], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;
        $redirectPath = ($user->role === 'admin') ? '/admin' : '/dashboard';

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'redirect_to' => $redirectPath 
            ]
        ]);
    }

    public function updateProfile(Request $request){
        $request->validate(['name' => 'required|string|max:255']);
        $request->user()->update(['name' => $request->name]);
        return response()->json(['message' => 'Perfil actualizado']);
    }

    public function destroy(Request $request){
        $request->user()->tokens()->delete();
        $request->user()->delete();
        return response()->json(['message' => 'Cuenta eliminada']);
    }
}