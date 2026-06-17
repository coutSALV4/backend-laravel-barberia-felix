<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'icon'         => $this->icon,
            'name'         => $this->name,
            'description'  => $this->description,
            'price'        => (float) $this->price,
            'duration_min' => $this->duration_min,
            'active'       => $this->active,
            'price_at_time' => $this->whenPivotLoaded(
                'appointment_services',
                fn() => (float) $this->pivot->price_at_time
            ),
        ];
    }
}