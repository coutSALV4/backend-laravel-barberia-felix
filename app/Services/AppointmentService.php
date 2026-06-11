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
        $services = Service::whereIn('id', $data['service_ids'])->get();
        $totalMin = $services->sum('duration_min');
        $totalPrice = $services->sum('price');

        $startTime = $data['start_time'];
        $endTime = Carbon::parse($startTime)->addMinutes($totalMin)->format('H:i:s');

        $this->assertNoConflict(
            barberId: $data['barber_id'],
            date: $data['appointment_date'],
            startTime: $startTime,
            endTime: $endTime,
            excludeId: null,
        );

        return DB::transaction(function () use ($data, $services, $startTime, $endTime, $totalPrice) {
            $appointment = Appointment::create([
                'client_id' => $data['client_id'],
                'barber_id' => $data['barber_id'],
                'receptionist_id' => auth()->id(),
                'appointment_date' => $data['appointment_date'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'notes' => $data['notes'] ?? null,
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            // Adjuntar servicios con el precio histórico del momento
            $pivot = $services->mapWithKeys(
                fn($s) => [$s->id => ['price_at_time' => $s->price]]
            )->toArray();

            $appointment->services()->attach($pivot);

            return $appointment->load('client', 'barber', 'services');
        });
    }

    public function update(Appointment $appointment, array $data): Appointment
    {
        $rescheduling = isset($data['barber_id'])
            || isset($data['appointment_date'])
            || isset($data['start_time']);

        // Calcular end_time con los datos actualizados ANTES de persistir nada.
        if ($rescheduling) {
            $barberId = $data['barber_id'] ?? $appointment->barber_id;
            $date = $data['appointment_date'] ?? $appointment->appointment_date;
            $startTime = $data['start_time'] ?? $appointment->start_time;
            $totalMin = $appointment->services->sum('duration_min');
            $endTime = Carbon::parse($startTime)->addMinutes($totalMin)->format('H:i:s');

            // Verificar conflictos antes de tocar la base de datos.
            $this->assertNoConflict(
                barberId: $barberId,
                date: $date,
                startTime: $startTime,
                endTime: $endTime,
                excludeId: $appointment->id,
            );
        }

        // Solo actualizamos una vez, incluyendo end_time si corresponde.
        $appointment->update(array_filter([
            'barber_id' => $data['barber_id'] ?? null,
            'appointment_date' => $data['appointment_date'] ?? null,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $rescheduling ? $endTime : null,
            'notes' => array_key_exists('notes', $data) ? $data['notes'] : null,
        ], fn($v) => $v !== null));

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
            ->when($filters['date'] ?? null, fn($q, $d) => $q->forDate($d))
            ->when($filters['barber_id'] ?? null, fn($q, $id) => $q->forBarber($id))
            ->when($filters['status'] ?? null, fn($q, $s) => $q->where('status', $s))
            ->paginate(10);
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Lanza una excepción si existe alguna cita activa del barbero que se
     * solape con el rango [startTime, endTime] en la fecha indicada.
     *
     * Dos rangos [A,B] y [C,D] se solapan cuando: A < D && C < B
     * (el inicio de uno es anterior al fin del otro, y viceversa).
     * Esto cubre solapamiento parcial, contenido y envolvente.
     */
    private function assertNoConflict(
        int $barberId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeId,
    ): void {
        $conflict = Appointment::forBarber($barberId)
            ->forDate($date)
            ->active()
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->where('start_time', '<', $endTime)   // la existente empieza antes de que termine la nueva
            ->where('end_time', '>', $startTime)  // la existente termina después de que empiece la nueva
            ->exists();

        if ($conflict) {
            throw new \Exception('El barbero no está disponible en ese horario.');
        }
    }
}