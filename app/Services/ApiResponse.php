<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(
        $data = null, 
        string $message = 'Operación exitosa', 
        int $code = 200
    ): JsonResponse 
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    public static function error(
        string $message = 'Error en la operación', 
        int $code = 400, 
    ): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }

    public static function tokenResponse(
        $user, 
        string $token, 
        bool $mustChangePassword = false
    ): JsonResponse
    {
        return response()->json([
            'success'             => true,
            'message'             => 'Inicio de sesión exitoso.',
            'access_token'        => $token,
            'token_type'          => 'Bearer',
            'must_change_password'=> $mustChangePassword,
            'user'                => $user,
        ]);
    }
}
