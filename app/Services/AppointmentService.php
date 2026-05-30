<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    public function create(array $data): Appointment
    {
        // 1. Cargar servicios seleccionados
        $services   = Service::whereIn('id', $data['service_ids'])->get();
        $totalMin   = $services->sum('duration_min');
        $totalPrice = $services->sum('price');

        // 2. Calcular end_time automáticamente
        $start   = Carbon::parse($data['start_time']);
        $endTime = $start->copy()->addMinutes($totalMin)->format('H:i');

        // 3. Verificar que el barbero esté disponible en ese horario
        $conflict = Appointment::forBarber($data['barber_id'])
            ->forDate($data['appointment_date'])
            ->active()
            ->where(function ($q) use ($data, $endTime) {
                $q->whereBetween('start_time', [$data['start_time'], $endTime])
                  ->orWhereBetween('end_time',  [$data['start_time'], $endTime]);
            })->exists();

        if ($conflict) {
            throw new \Exception('El barbero no está disponible en ese horario.');
        }

        // 4. Crear todo en una transacción (si algo falla, se revierte todo)
        return DB::transaction(function () use ($data, $services, $endTime, $totalPrice) {
            $appointment = Appointment::create([
                'client_id'        => $data['client_id'],
                'barber_id'        => $data['barber_id'],
                'receptionist_id'  => auth()->id(),
                'appointment_date' => $data['appointment_date'],
                'start_time'       => $data['start_time'],
                'end_time'         => $endTime,
                'notes'            => $data['notes'] ?? null,
                'total_price'      => $totalPrice,
                'status'           => 'pending',
            ]);

            // 5. Adjuntar servicios con el precio histórico del momento
            $pivot = $services->mapWithKeys(
                fn($s) => [$s->id => ['price_at_time' => $s->price]]
            )->toArray();

            $appointment->services()->attach($pivot);

            return $appointment->load('client', 'barber', 'services');
        });
    }

    public function update(Appointment $appointment, array $data): Appointment
    {
        // Solo se pueden actualizar ciertos campos
        $appointment->update([
            'barber_id'        => $data['barber_id'] ?? $appointment->barber_id,
            'appointment_date' => $data['appointment_date'] ?? $appointment->appointment_date,
            'start_time'       => $data['start_time'] ?? $appointment->start_time,
            'notes'            => $data['notes'] ?? $appointment->notes,
        ]);

        // Si se actualizó el horario o el barbero, recalcular end_time y verificar conflictos
        if (isset($data['barber_id']) || isset($data['appointment_date']) || isset($data['start_time'])) {
            $services   = $appointment->services;
            $totalMin   = $services->sum('duration_min');
            $endTime    = Carbon::parse($appointment->start_time)->addMinutes($totalMin)->format('H:i');

            // Verificar conflictos con el nuevo horario/barbero
            $conflict = Appointment::forBarber($appointment->barber_id)
                ->forDate($appointment->appointment_date)
                ->active()
                ->where('id', '!=', $appointment->id)
                ->where(function ($q) use ($appointment, $endTime) {
                    $q->whereBetween('start_time', [$appointment->start_time, $endTime])
                      ->orWhereBetween('end_time',  [$appointment->start_time, $endTime]);
                })->exists();

            if ($conflict) {
                throw new \Exception('El barbero no está disponible en ese horario.');
            }

            // Actualizar end_time si cambió el horario o el barbero
            if (isset($data['barber_id']) || isset($data['appointment_date']) || isset($data['start_time'])) {
                $appointment->update(['end_time' => $endTime]);
            }
        }

        return $appointment->fresh(['client', 'barber', 'services']);
    }

    public function updateStatus(Appointment $appointment, string $status): Appointment
    {
        $appointment->update(['status' => $status]);
        return $appointment->fresh(['client', 'barber', 'services']);
    }

    public function getAll(array $filters = [])
    {
        return Appointment::with(['client', 'barber', 'services', 'payment'])
            ->when($filters['date']      ?? null, fn($q, $d)  => $q->forDate($d))
            ->when($filters['barber_id'] ?? null, fn($q, $id) => $q->forBarber($id))
            ->when($filters['status']    ?? null, fn($q, $s)  => $q->where('status', $s))
            ->paginate(10);
    }
}