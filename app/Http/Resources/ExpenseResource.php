<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'description'  => $this->description,
            'amount'       => (float) $this->amount,
            'expense_date' => $this->expense_date->toDateString(),
            'category'     => $this->whenLoaded('category', fn() => [
                'id'    => $this->category->id,
                'name'  => $this->category->name,
                'color' => $this->category->color,
            ]),
            'registered_by' => UserResource::make($this->whenLoaded('registeredBy')),
            'created_at'    => $this->created_at->toDateTimeString(),
        ];
    }
}