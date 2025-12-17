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
    public function redirectToGoogle(){
        return Socialite::driver('google')->stateless()->redirect();
    }

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