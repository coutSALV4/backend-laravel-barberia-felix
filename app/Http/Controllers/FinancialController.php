<?php

namespace App\Http\Controllers;

use App\Services\ApiResponse;
use App\Services\FinancialReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FinancialController extends Controller
{
    public function __construct(
        private FinancialReportService $service,
        private ApiResponse            $apiResponse,
    ) {}

    public function summary(Request $request): JsonResponse
    {
        if (auth()->user()->role !== 'admin')
            return $this->apiResponse->error(
                'No tienes permiso para ver reportes financieros.',
                Response::HTTP_FORBIDDEN
            );

        $request->validate([
            'from' => ['required', 'date'],
            'to'   => ['required', 'date', 'after_or_equal:from'],
        ]);

        $report = $this->service->summary(
            Carbon::parse($request->from),
            Carbon::parse($request->to),
        );

        return $this->apiResponse->success(
            $report,
            'Reporte financiero generado exitosamente',
            Response::HTTP_OK
        );
    }
}