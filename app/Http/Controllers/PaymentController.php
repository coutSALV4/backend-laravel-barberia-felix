<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Services\ApiResponse;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $service,
        private ApiResponse    $apiResponse,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $payments = $this->service->getAll(
            $request->only(['appointment_id', 'status', 'method'])
        );

        return $this->apiResponse->success(
            PaymentResource::collection($payments),
            'Pagos obtenidos exitosamente',
            Response::HTTP_OK
        );
    }

    public function store(StorePaymentRequest $request): JsonResponse
    {
        try {
            $payment = $this->service->register($request->validated());

            return $this->apiResponse->success(
                new PaymentResource($payment),
                'Pago registrado exitosamente',
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->apiResponse->error($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}