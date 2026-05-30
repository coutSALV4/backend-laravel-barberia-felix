<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Payment;
use Carbon\Carbon;

class FinancialReportService
{
    public function summary(Carbon $from, Carbon $to): array
    {
        // Total de ingresos en el período
        $income = Payment::whereBetween('paid_at', [$from, $to])
            ->where('status', 'completed')
            ->sum('amount');

        // Total de egresos en el período
        $expenses = Expense::whereBetween('expense_date', [$from, $to])
            ->sum('amount');

        // Ingresos agrupados por barbero
        $byBarber = Payment::with('appointment.barber')
            ->whereBetween('paid_at', [$from, $to])
            ->where('status', 'completed')
            ->get()
            ->groupBy('appointment.barber.name')
            ->map(fn($payments) => $payments->sum('amount'));

        // Egresos agrupados por categoría
        $byCategory = Expense::with('category')
            ->whereBetween('expense_date', [$from, $to])
            ->get()
            ->groupBy('category.name')
            ->map(fn($expenses) => $expenses->sum('amount'));

        return [
            'period'      => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'income'      => (float) $income,
            'expenses'    => (float) $expenses,
            'net_profit'  => (float) ($income - $expenses),
            'by_barber'   => $byBarber,
            'by_category' => $byCategory,
        ];
    }
}