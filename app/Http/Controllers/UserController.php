<?php

namespace App\Http\Controllers;

use App\Services\ApiResponse;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\Auth\UserRequest;
use App\Http\Resources\UserResource;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct(
        private UserService $service, 
        private ApiResponse $apiResponse,
        ) {}

    public function index(Request $request)
    {
        $role = $request->query('role');
        $users = $this->service->getAllUsers($role);
        return $this->apiResponse->success(
            UserResource::collection($users), 
            'Usuarios obtenidos exitosamente', 
            Response::HTTP_OK);
    }

    public function update(UserRequest $request, int $id)
    {
        $user = $this->service->getUserById($id);
        $updatedUser = $this->service->updateUser($user, $request->validated());

        return $this->apiResponse->success(
            new UserResource($updatedUser), 
            'Usuario actualizado exitosamente', 
            Response::HTTP_OK);
    }

    public function destroy(User $id)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') 
            return $this->apiResponse->error('No tienes permiso para eliminar usuarios.', Response::HTTP_FORBIDDEN);

        $id->delete();
        return $this->apiResponse->success(
            null, 
            'Usuario eliminado exitosamente', 
            Response::HTTP_OK);
    }
}
