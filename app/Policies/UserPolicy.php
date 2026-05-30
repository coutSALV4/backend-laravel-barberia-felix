<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /** Admins y recepcionistas pueden listar usuarios */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'receptionist']);
    }

    /** Cualquiera puede ver su propio perfil; admins ven todos */
    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->role === 'admin';
    }

    /** Solo admins crean usuarios */
    public function create(User $authUser): bool
    {
        return $authUser->role === 'admin';
    }

    /** Cada usuario puede editarse a sí mismo; admins editan a todos */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->role === 'admin';
    }

    /** Solo admins eliminan usuarios */
    public function delete(User $authUser, User $targetUser): bool
    {
        return $authUser->role === 'admin' && $authUser->id !== $targetUser->id;
    }

    public function restore(User $user, User $model): bool
    {
        return $user->role === 'admin';
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->role === 'admin';
    }
}