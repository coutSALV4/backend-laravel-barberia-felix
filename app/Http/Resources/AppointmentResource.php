<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'status'           => $this->status,
            'appointment_date' => $this->appointment_date->toDateString(),
            'start_time'       => $this->start_time,
            'end_time'         => $this->end_time,
            'total_price'      => (float) $this->total_price,
            'notes'            => $this->notes,
            'client'           => UserResource::make($this->whenLoaded('client')),
            'barber'           => UserResource::make($this->whenLoaded('barber')),
            'services'         => ServiceResource::collection($this->whenLoaded('services')),
            'payment'          => PaymentResource::make($this->whenLoaded('payment')),
        ];
    }
}