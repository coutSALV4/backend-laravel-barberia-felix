<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    private const DEFAULT_PASSWORD = '12345678';

    // -------------------------------------------------------------------------
    // REGISTER (solo admin)
    // -------------------------------------------------------------------------

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'               => $request->validated('name'),
            'lastname'           => $request->validated('lastname'),
            'email'              => $request->validated('email'),
            'phone'              => $request->validated('phone'),
            'role'               => $request->validated('role'),
            'password'           => Hash::make(self::DEFAULT_PASSWORD),
        ]);

        Log::info('User created by admin', [
            'created_by' => auth()->id(),
            'new_user_id' => $user->id,
            'email' => $user->email,
        ]);

        return response()->json([
            'message' => 'usuario creado con contraseña por defecto ' . self::DEFAULT_PASSWORD . '.',
            'data'    => new UserResource($user),
        ], Response::HTTP_CREATED);
    }

    // -------------------------------------------------------------------------
    // LOGIN
    // -------------------------------------------------------------------------

    public function login(LoginRequest $request): JsonResponse
    {
        $key = 'login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => "Demasiados intentos de inicio de sesión. Inténtalo de nuevo en {$seconds} segundos.",
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($key, 60);
            return response()->json([
                'message' => 'Credenciales inválidas.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        RateLimiter::clear($key);

        /** @var User $user */
        $user = Auth::user();

        $user->tokens()->delete();

        $token = $user->createToken(
            'auth_token',
            $this->abilitiesForRole($user->role)
        )->plainTextToken;

        Log::info('User logged in', ['user_id' => $user->id]);

        return response()->json([
            'message'              => 'Inicio de sesión exitoso.',
            'access_token'         => $token,
            'token_type'           => 'Bearer',
            'must_change_password' => (bool) $user->must_change_password,
            'user'                 => new UserResource($user),
        ]);
    }

    // -------------------------------------------------------------------------
    // LOGOUT
    // -------------------------------------------------------------------------

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        Log::info('User logged out', ['user_id' => $request->user()->id]);

        return response()->json([
            'message' => 'Cierre de sesión exitoso.',
        ]);
    }

    // -------------------------------------------------------------------------
    // CHANGE PASSWORD
    // -------------------------------------------------------------------------

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'     => [
                'required', 'string', 'min:8', 'confirmed',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
        ]);

        /** @var User $user */
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->update([
            'password'             => Hash::make($request->new_password),
            'must_change_password' => false,
        ]);

        // Revocar todos los tokens para forzar nuevo login con la contraseña actualizada
        $user->tokens()->delete();

        Log::info('User changed password', ['user_id' => $user->id]);

        return response()->json([
            'message' => 'Contraseña actualizada correctamente. Por favor, inicie sesión de nuevo.',
        ]);
    }

    // -------------------------------------------------------------------------
    // HELPERS PRIVADOS
    // -------------------------------------------------------------------------

    private function abilitiesForRole(string $role): array
    {
        return match ($role) {
            'admin'        => ['*'],
            'barber'       => ['appointments:read', 'appointments:write', 'clients:read'],
            'receptionist' => ['appointments:read', 'appointments:write', 'clients:read', 'clients:write'],
            'client'       => ['appointments:read', 'appointments:create'],
            default        => [],
        };
    }
}