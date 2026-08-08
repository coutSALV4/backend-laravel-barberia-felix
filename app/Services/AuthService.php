<?php

namespace App\Services;

use App\Models\User;
use App\Services\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;
use App\Models\PasswordResetCode;
use Illuminate\Support\Str;


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
                'code' => Response::HTTP_TOO_MANY_REQUESTS,
            ];
        }

        if (!Auth::attempt($credentials)) {
            RateLimiter::hit($key, 60);
            return [
                'success' => false,
                'message' => 'Credenciales inválidas.',
                'code' => Response::HTTP_UNAUTHORIZED,
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
            'success' => true,
            'user' => $user,
            'token' => $token,
            'must_change_password' => (bool) $user->must_change_password,
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
                'code' => Response::HTTP_UNAUTHORIZED,
            ];
        }

        $user->update([
            'password' => Hash::make($newPassword),
            'must_change_password' => false,
        ]);

        $user->tokens()->delete();

        return ['success' => true];
    }

    private function abilitiesForRole(string $role): array
    {
        return match ($role) {
            'admin' => ['*'],
            'barber' => ['appointments:read', 'appointments:write', 'clients:read'],
            'receptionist' => ['appointments:read', 'appointments:write', 'clients:read', 'clients:write'],
            'client' => ['appointments:read', 'appointments:create'],
            default => [],
        };
    }

    public function sendResetCode(string $email): array
    {
        $user = User::where('email', $email)->first();

        // Respuesta genérica sin importar si el correo existe, para no filtrar qué correos están registrados
        $genericResponse = [
            'success' => true,
            'message' => 'Si el correo está registrado, recibirás un código de verificación.',
        ];

        if (!$user) {
            return $genericResponse;
        }

        $existing = PasswordResetCode::where('email', $email)->first();

        // Evita reenviar un código nuevo si el anterior tiene menos de 60 segundos
        if ($existing && $existing->created_at->gt(now()->subSeconds(60))) {
            return $genericResponse;
        }

        $code = (string) random_int(100000, 999999);

        PasswordResetCode::updateOrCreate(
            ['email' => $email],
            [
                'code' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(10),
            ]
        );

        $user->notify(new \App\Notifications\ResetPasswordCodeNotification($code));

        return $genericResponse;
    }

    public function verifyResetCode(string $email, string $code): array
    {
        $record = PasswordResetCode::where('email', $email)->first();

        if (!$record) {
            return ['success' => false, 'message' => 'Código inválido o expirado.'];
        }

        if ($record->expires_at->isPast()) {
            $record->delete();
            return ['success' => false, 'message' => 'El código ha expirado. Solicita uno nuevo.'];
        }

        if ($record->attempts >= 5) {
            $record->delete();
            return ['success' => false, 'message' => 'Demasiados intentos. Solicita un nuevo código.'];
        }

        if (!Hash::check($code, $record->code)) {
            $record->increment('attempts');
            return ['success' => false, 'message' => 'Código incorrecto.'];
        }

        return ['success' => true, 'message' => 'Código válido.'];
    }

    public function resetPasswordWithCode(string $email, string $code, string $newPassword): array
    {
        $verification = $this->verifyResetCode($email, $code);

        if (!$verification['success']) {
            return $verification;
        }

        $user = User::where('email', $email)->first();

        $user->update([
            'password' => Hash::make($newPassword),
            'must_change_password' => false,
        ]);

        $user->tokens()->delete(); // cierra sesiones activas de Sanctum

        PasswordResetCode::where('email', $email)->delete();

        return [
            'success' => true,
            'message' => 'Contraseña restablecida exitosamente. Inicia sesión de nuevo.',
        ];
    }
}