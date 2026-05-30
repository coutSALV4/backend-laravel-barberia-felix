<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    /** Admins y recepcionistas ven todas las citas; barberos solo las suyas; clientes solo las suyas */
    public function viewAny(User $user): bool
    {
        return true; // Todos pueden listar; el Service filtra según rol
    }

    /** Un usuario puede ver la cita si es su cita como cliente, su cita como barbero, o es admin/recepcionista */
    public function view(User $user, Appointment $appointment): bool
    {
        return in_array($user->role, ['admin', 'receptionist'])
            || $appointment->client_id === $user->id
            || $appointment->barber_id === $user->id;
    }

    /** Admins, recepcionistas y clientes pueden crear citas */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'receptionist', 'client']);
    }

    /** Admins y recepcionistas pueden actualizar cualquier cita; barberos solo su status */
    public function update(User $user, Appointment $appointment): bool
    {
        return in_array($user->role, ['admin', 'receptionist'])
            || $appointment->barber_id === $user->id;
    }

    /** Solo admins y recepcionistas pueden cancelar (soft delete) */
    public function delete(User $user, Appointment $appointment): bool
    {
        return in_array($user->role, ['admin', 'receptionist']);
    }

    public function restore(User $user, Appointment $appointment): bool
    {
        return $user->role === 'admin';
    }

    public function forceDelete(User $user, Appointment $appointment): bool
    {
        return $user->role === 'admin';
    }
}