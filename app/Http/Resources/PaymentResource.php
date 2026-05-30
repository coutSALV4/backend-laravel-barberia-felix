<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'amount'      => (float) $this->amount,
            'method'      => $this->method,
            'status'      => $this->status,
            'reference'   => $this->reference,
            'paid_at'     => $this->paid_at?->toDateTimeString(),
            'received_by' => UserResource::make($this->whenLoaded('receivedBy')),
        ];
    }
}