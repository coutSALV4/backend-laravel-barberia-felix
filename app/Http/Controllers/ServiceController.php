<?php

namespace App\Http\Controllers;

use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Services\ApiResponse;
use App\Services\LoggerService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ServiceController extends Controller
{
    public function __construct(
        private ApiResponse $apiResponse,
    ) {}

    public function index(): JsonResponse
    {
        $services = Service::active()->get();
        return $this->apiResponse->success(
            ServiceResource::collection($services),
            'Servicios obtenidos exitosamente',
            Response::HTTP_OK
        );
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        if (auth()->user()->role !== 'admin')
            return $this->apiResponse->error('No tienes permiso para crear servicios.', Response::HTTP_FORBIDDEN);

        $service = Service::create($request->validated());
        return $this->apiResponse->success(
            new ServiceResource($service),
            'Servicio creado exitosamente',
            Response::HTTP_CREATED
        );
    }

    public function update(StoreServiceRequest $request, Service $id): JsonResponse
    {
        if (auth()->user()->role !== 'admin')
            return $this->apiResponse->error('No tienes permiso para editar servicios.', Response::HTTP_FORBIDDEN);

        $id->update($request->validated());
        return $this->apiResponse->success(
            new ServiceResource($id),
            'Servicio actualizado exitosamente',
            Response::HTTP_OK
        );
    }

    public function destroy(Service $id): JsonResponse
    {
        if (auth()->user()->role !== 'admin')
            return $this->apiResponse->error('No tienes permiso para eliminar servicios.', Response::HTTP_FORBIDDEN);

        $id->delete(); // Soft delete
        return $this->apiResponse->success(null, 'Servicio eliminado exitosamente', Response::HTTP_OK);
    }
}