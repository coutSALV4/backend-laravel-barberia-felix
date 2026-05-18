<?php

namespace App\Services;

use App\Models\User;
use App\Services\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AuthService
{
    public function create(array $data): User
    {
        return User::create($data + ['password' => User::DEFAULT_PASSWORD]);
    }

    public function attemptLogin(array $credentials, string $ip): array
    {
        $key = 'login:' . $ip;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return [
                'success' => false,
                'message' => "Demasiados intentos de inicio de sesión. Inténtalo de nuevo en {$seconds} segundos.",
                'code'    => Response::HTTP_TOO_MANY_REQUESTS,
            ];
        }

        if (!Auth::attempt($credentials)) {
            RateLimiter::hit($key, 60);
            return [
                'success' => false,
                'message' => 'Credenciales inválidas.',
                'code'    => Response::HTTP_UNAUTHORIZED,
            ];
        }

        RateLimiter::clear($key);

        /** @var User $user */
        $user = Auth::user();

        // Revocar tokens anteriores para sesión única
        $user->tokens()->delete();

        $token = $user->createToken(
            'auth_token',
            $this->abilitiesForRole($user->role)
        )->plainTextToken;

        return [
            'success'             => true,
            'user'                => $user,
            'token'               => $token,
            'must_change_password'=> (bool) $user->must_change_password,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): array
    {
        if (!Hash::check($currentPassword, $user->password)) {
            return [
                'success' => false,
                'message' => 'La contraseña actual es incorrecta.',
                'code'    => Response::HTTP_UNAUTHORIZED,
            ];
        }

        $user->update([
            'password'             => Hash::make($newPassword),
            'must_change_password' => false,
        ]);

        $user->tokens()->delete();

        return ['success' => true];
    }
    
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