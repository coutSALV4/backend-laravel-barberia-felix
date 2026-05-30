<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Services\ApiResponse;
use App\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExpenseController extends Controller
{
    public function __construct(
        private ExpenseService $service,
        private ApiResponse    $apiResponse,
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (!in_array(auth()->user()->role, ['admin', 'receptionist']))
            return $this->apiResponse->error('No tienes permiso para ver gastos.', Response::HTTP_FORBIDDEN);

        $expenses = $this->service->getAll(
            $request->only(['category_id', 'from', 'to'])
        );

        return $this->apiResponse->success(
            ExpenseResource::collection($expenses),
            'Gastos obtenidos exitosamente',
            Response::HTTP_OK
        );
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        if (!in_array(auth()->user()->role, ['admin', 'receptionist']))
            return $this->apiResponse->error('No tienes permiso para registrar gastos.', Response::HTTP_FORBIDDEN);

        $expense = $this->service->create(
            $request->validated(),
            $request->file('receipt')
        );

        return $this->apiResponse->success(
            new ExpenseResource($expense->load('category', 'registeredBy')),
            'Gasto registrado exitosamente',
            Response::HTTP_CREATED
        );
    }

    public function destroy(Expense $id): JsonResponse
    {
        if (auth()->user()->role !== 'admin')
            return $this->apiResponse->error('No tienes permiso para eliminar gastos.', Response::HTTP_FORBIDDEN);

        $id->delete();

        return $this->apiResponse->success(null, 'Gasto eliminado exitosamente', Response::HTTP_OK);
    }
}