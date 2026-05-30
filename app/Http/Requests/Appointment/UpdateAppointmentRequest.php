<?php

namespace App\Http\Requests\Appoinment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'barber_id'        => ['sometimes', 'exists:users,id', 'different:client_id'],
            'service_ids'      => ['sometimes', 'array', 'min:1'],
            'service_ids.*'    => ['exists:services,id'],
            'appointment_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'start_time'       => ['sometimes', 'date_format:H:i'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            response()->json(['errors' => $validator->errors()], 422)
        );
    }
}