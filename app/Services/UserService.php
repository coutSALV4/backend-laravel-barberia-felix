<?php

namespace App\Services;

use App\Models\User;

class UserService
{


    public function getAllUsers(?string $role = null)
    {
        $query = User::query();

        if ($role) {
            $query->where('role', $role);
        }

        return $query->get();
    }

    public function getUserById($id)
    {
        return User::findOrFail($id);
    }

    public function updateUser(User $user, array $data)
    {
        $user->update($data);
        return $user;
    }

    public function deleteUser(User $user)
    {
        $user->delete();
        return true;
    }

}
