<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\UserRequest;
use App\Http\Requests\Auth\PasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\AuthService;
use App\Services\LoggerService;
use App\Services\ApiResponse;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $service, 
        private LoggerService $loggerService, 
        private ApiResponse $apiResponse
        ) {}

    public function register(UserRequest $request): JsonResponse
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') 
            return $this->apiResponse->error('No tienes permiso para crear usuarios.', Response::HTTP_FORBIDDEN);
        
        $user = $this->service->create($request->validated());
        $this->loggerService->userCreatedByAdmin($user);
        return $this->apiResponse->success(
            new UserResource($user),
            'usuario creado con contraseña por defecto ' . User::DEFAULT_PASSWORD . '.',
            Response::HTTP_CREATED
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->service->attemptLogin($request->only('email', 'password'), $request->ip());
        if (!$result['success']) 
            return $this->apiResponse->error($result['message'], $result['code']);

        $user = $result['user'];

        $this->loggerService->userLoggedIn($user);

        return $this->apiResponse->tokenResponse(
            new UserResource($user),
            $result['token'],
            $result['must_change_password']
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->service->logout($user);
        $this->loggerService->userLoggedOut($user);

        return $this->apiResponse->success(null, 'Cierre de sesión exitoso.', Response::HTTP_OK);
    }

    public function changePassword(PasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        $result = $this->service->changePassword($user, $request->current_password, $request->new_password);

        if (!$result['success'])
            return $this->apiResponse->error($result['message'], $result['code']);
        
        $this->loggerService->userChangedPassword($user);

        return $this->apiResponse->success(null, 'Contraseña actualizada correctamente. Por favor, inicie sesión de nuevo.', Response::HTTP_OK);
    }
}