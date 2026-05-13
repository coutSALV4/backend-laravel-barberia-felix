<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'lastname'   => $this->lastname,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'role'       => $this->role,
            'created_at' => $this->created_at,
        ];
    }
}