<?php

namespace App\Http\Controllers;

use App\Services\ApiResponse;
use App\Services\UserService;
use App\Services\LoggerService;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\UserRequest;
use App\Http\Resources\UserResource;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct(
        private UserService $service, 
        private LoggerService $loggerService, 
        private ApiResponse $apiResponse,
        ) {}

    public function index(Request $request)
    {
        $role = $request->query('role');
        $users = $this->service->getAllUsers($role);
        $this->loggerService->index();

        return $this->apiResponse->success(
            UserResource::collection($users), 
            'Usuarios obtenidos exitosamente', 
            Response::HTTP_OK);
    }

    public function update(UserRequest $request, int $id)
    {
        $user = $this->service->getUserById($id);
        $updatedUser = $this->service->updateUser($user, $request->validated());
        $this->loggerService->update($id);

        return $this->apiResponse->success(
            new UserResource($updatedUser), 
            'Usuario actualizado exitosamente', 
            Response::HTTP_OK);
    }

    public function destroy(int $id)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') 
            return $this->apiResponse->error('No tienes permiso para eliminar usuarios.', Response::HTTP_FORBIDDEN);

        $user = $this->service->getUserById($id);
        $this->service->deleteUser($user);
        $this->loggerService->destroy($id);
        return $this->apiResponse->success(
            null, 
            'Usuario eliminado exitosamente', 
            Response::HTTP_OK);
    }
}
