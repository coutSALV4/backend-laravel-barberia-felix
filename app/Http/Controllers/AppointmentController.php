<?php

namespace App\Http\Controllers;

use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Requests\Appoinment\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\ApiResponse;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppointmentController extends Controller
{
    public function __construct(
        private AppointmentService $service,
        private ApiResponse        $apiResponse,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $appointments = $this->service->getAll($request->only(['date', 'barber_id', 'status']));

        return $this->apiResponse->success(
            AppointmentResource::collection($appointments),
            'Citas obtenidas exitosamente',
            Response::HTTP_OK
        );
    }

    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        try {
            $appointment = $this->service->create($request->validated());
            return $this->apiResponse->success(
                new AppointmentResource($appointment),
                'Cita creada exitosamente',
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->apiResponse->error($e->getMessage(), Response::HTTP_CONFLICT);
        }
    }

    public function update(UpdateAppointmentRequest $request, Appointment $id): JsonResponse
    {
        try {
            $appointment = $this->service->update($id, $request->validated());
            return $this->apiResponse->success(
                new AppointmentResource($appointment),
                'Cita actualizada exitosamente',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->apiResponse->error($e->getMessage(), Response::HTTP_CONFLICT);
        }
    }

    public function updateStatus(Request $request, Appointment $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending,confirmed,in_progress,completed,cancelled,no_show'],
        ]);

        $appointment = $this->service->updateStatus($id, $request->status);

        return $this->apiResponse->success(
            new AppointmentResource($appointment),
            'Estado de cita actualizado',
            Response::HTTP_OK
        );
    }

    public function destroy(Appointment $id): JsonResponse
    {
        $id->delete(); // Soft delete
        return $this->apiResponse->success(null, 'Cita cancelada exitosamente', Response::HTTP_OK);
    }
}