<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeamFilterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'competition_id' => ['nullable', 'integer', 'exists:competitions,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'competition_id.exists' => 'La competición seleccionada no existe.',
            'per_page.max' => 'Se pueden pedir como máximo 100 registros por página.',
        ];
    }
}
