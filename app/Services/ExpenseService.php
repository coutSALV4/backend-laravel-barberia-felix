<?php

namespace App\Services;

use App\Models\Expense;

class ExpenseService
{

    public function create(array $data, $receiptFile = null): Expense
    {
        $driveReceiptId = null;

        return Expense::create([
            'registered_by'    => auth()->id(),
            'category_id'      => $data['category_id'],
            'description'      => $data['description'],
            'amount'           => $data['amount'],
            'expense_date'     => $data['expense_date'],
            'drive_receipt_id' => $driveReceiptId,
        ]);
    }

    public function getAll(array $filters = [])
    {
        return Expense::with(['category', 'registeredBy'])
            ->when($filters['category_id'] ?? null, fn($q, $id) => $q->where('category_id', $id))
            ->when($filters['from'] ?? null, fn($q, $d) => $q->whereDate('expense_date', '>=', $d))
            ->when($filters['to'] ?? null, fn($q, $d) => $q->whereDate('expense_date', '<=', $d))
            ->orderByDesc('expense_date')
            ->paginate(20);
    }
}