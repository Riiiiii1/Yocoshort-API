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
    /**
     * @OA\Post(
     * path="/api/register",
     * summary="Registrar nuevo usuario",
     * tags={"Auth"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", example="Juan Pérez"),
     * @OA\Property(property="email", type="string", format="email", example="juan@test.com"),
     * @OA\Property(property="password", type="string", format="password", example="secret123")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Usuario registrado",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Usuario registrado exitosamente..."),
     * @OA\Property(property="data", type="object")
     * )
     * )
     * )
     */
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
    /**
     * @OA\Get(
     * path="/api/verify-email/{token}",
     * summary="Verificar correo electrónico",
     * description="Este endpoint recibe el token del email y redirige al Frontend.",
     * tags={"Auth"},
     * @OA\Parameter(
     * name="token",
     * in="path",
     * required=true,
     * description="Token de verificación enviado por correo",
     * @OA\Schema(type="string")
     * ),
     * @OA\Response(
     * response=302,
     * description="Redirección al Frontend"
     * )
     * )
     */
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
    /**
     * @OA\Post(
     * path="/api/login",
     * summary="Iniciar sesión",
     * tags={"Auth"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="email", type="string", format="email", example="juan@test.com"),
     * @OA\Property(property="password", type="string", format="password", example="secret123")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Login exitoso",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Login exitoso"),
     * @OA\Property(
     * property="data",
     * type="object",
     * @OA\Property(property="access_token", type="string", example="1|AbCdEf..."),
     * @OA\Property(property="token_type", type="string", example="Bearer"),
     * @OA\Property(property="redirect_to", type="string", example="/dashboard")
     * )
     * )
     * ),
     * @OA\Response(response=401, description="Credenciales incorrectas"),
     * @OA\Response(response=403, description="Email no verificado")
     * )
     */
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
    /**
     * @OA\Put(
     * path="/api/user",
     * summary="Actualizar perfil de usuario",
     * tags={"Auth"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", example="Juan Nuevo")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Perfil actualizado",
     * @OA\JsonContent(@OA\Property(property="message", type="string", example="Perfil actualizado"))
     * )
     * )
     */
    public function updateProfile(Request $request){
        $request->validate(['name' => 'required|string|max:255']);
        $request->user()->update(['name' => $request->name]);
        return response()->json(['message' => 'Perfil actualizado']);
    }
    /**
     * @OA\Delete(
     * path="/api/user",
     * summary="Eliminar cuenta de usuario",
     * tags={"Auth"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Cuenta eliminada",
     * @OA\JsonContent(@OA\Property(property="message", type="string", example="Cuenta eliminada"))
     * )
     * )
     */
    public function destroy(Request $request){
        $request->user()->tokens()->delete();
        $request->user()->delete();
        return response()->json(['message' => 'Cuenta eliminada']);
    }
}