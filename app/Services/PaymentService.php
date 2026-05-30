<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Payment;

class PaymentService
{
    public function register(array $data): Payment
    {
        $appointment = Appointment::findOrFail($data['appointment_id']);

        $payment = Payment::create([
            'appointment_id' => $appointment->id,
            'received_by'    => auth()->id(),
            'amount'         => $data['amount'],
            'method'         => $data['method'],
            'reference'      => $data['reference'] ?? null,
            'status'         => 'completed',
            'paid_at'        => $data['paid_at'] ?? now(),
        ]);

        // Si el pago cubre el total, marcar la cita como completada
        if ($payment->amount >= $appointment->total_price) {
            $appointment->update(['status' => 'completed']);
        }

        return $payment->load('receivedBy', 'appointment');
    }

    public function getAll(array $filters = [])
    {
        return Payment::with(['appointment.client', 'appointment.barber', 'receivedBy'])
            ->when($filters['appointment_id'] ?? null, fn($q, $id) => $q->where('appointment_id', $id))
            ->when($filters['status'] ?? null, fn($q, $s) => $q->where('status', $s))
            ->when($filters['method'] ?? null, fn($q, $m) => $q->where('method', $m))
            ->orderByDesc('paid_at')
            ->paginate(20);
    }
}