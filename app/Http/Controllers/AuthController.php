<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\VerifyEmail;

class AuthController extends Controller
{
    public function register(RegisterUserRequest $request): JsonResponse
    {
        try {
            // Generar token ANTES para evitar doble save()
            $verificationToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $verificationToken);

            $newUser = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verification_token' => $tokenHash,
            ]);

            $verificationUrl = url("/api/verify-email/{$verificationToken}");

            //Envio inmediato con send, pero podemos optimizar con un worker en  la versión final
            Mail::to($newUser->email)->send(new VerifyEmail($newUser, $verificationUrl));

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente. Verifica tu email.',
                'data' => [
                    'user' => [
                        'id' => $newUser->id,
                        'name' => $newUser->name,
                        'email' => $newUser->email,
                        'requires_verification' => true,
                    ]
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en el registro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verifyEmail($token): JsonResponse
    {
        try {
            $tokenHash = hash('sha256', $token);
            $user = User::where('email_verification_token', $tokenHash)
                ->whereNull('email_verified_at')
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token de verificación inválido o expirado.',
                ], 404);
            }

            $user->email_verified_at = now();
            $user->email_verification_token = null;
            $user->save();
            $accessToken = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => '✅ Email verificado exitosamente.',
                'data' => [
                    'access_token' => $accessToken,
                    'token_type' => 'Bearer',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la verificación: ' . $e->getMessage()
            ], 500);
        }
    }
}
