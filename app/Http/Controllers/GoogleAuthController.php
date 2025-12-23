<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * @OA\Get(
     * path="/auth/google/redirect-web",
     * summary="Iniciar sesión con Google",
     * description="Redirige al usuario a la pantalla de consentimiento de Google.",
     * tags={"Auth"},
     * @OA\Response(
     * response=302,
     * description="Redirección a los servidores de Google"
     * )
     * )
     */
    public function redirectToGoogle(){
        return Socialite::driver('google')->stateless()->redirect();
    }
    /**
     * @OA\Get(
     * path="/auth/google/callback",
     * summary="Callback de Google",
     * description="Maneja la respuesta de Google. Si el login es exitoso, redirige al Frontend con el token en la URL.",
     * tags={"Auth"},
     * @OA\Response(
     * response=302,
     * description="Redirección al Dashboard del Frontend (?token=...)"
     * ),
     * @OA\Response(
     * response=500,
     * description="Error en el servidor o cancelación del usuario"
     * )
     * )
     */
    public function handleGoogleCallback(Request $request){
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('email', $googleUser->getEmail())->first();
            
            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => now(),
                ]);
            } else {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            }

            $token = $user->createToken('google-auth-token')->plainTextToken;
            $frontendUrl = env('APP_ENV') === 'local' 
                ? 'http://localhost:3000' 
                : env('FRONTEND_URL');
            $redirectUrl = $frontendUrl . '/dashboard?token=' . urlencode($token);
            
            return redirect($redirectUrl);

        } catch (\Exception $e) {
            $frontendUrl = env('APP_ENV') === 'local' ? 'http://localhost:3000' : env('FRONTEND_URL');
            return redirect($frontendUrl . '/?error=' . urlencode($e->getMessage()));
        }
    }
}