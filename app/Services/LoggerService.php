<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LoggerService
{
    public function index(): void
    {
        Log::info("index method called in " . __CLASS__);
    }

    public function update($id): void
    {
        Log::info("update method called in " . __CLASS__, 
        ['user_id' => $id]);
    }

    public function destroy($id): void
    {
        Log::info("destroy method called in " . __CLASS__, 
        ['user_id' => $id]);
    }

    public function userCreatedByAdmin($user): void
    {
        Log::info('User created by admin', [
            'created_by' => auth()->id(),
            'new_user_id' => $user->id,
        ]);
    }

    public function userLoggedIn($user): void
    {
        Log::info('User logged in', [
            'user_id' => $user->id,
        ]);
    }

    public function userLoggedOut($user): void
    {
        Log::info('User logged out', [
            'user_id' => $user->id,
        ]);
    }

    public function userChangedPassword($user): void
    {
        Log::info('User changed password', [
            'user_id' => $user->id,
        ]);
    }
}