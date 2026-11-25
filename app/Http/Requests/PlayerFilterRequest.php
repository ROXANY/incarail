<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlayerFilterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'position' => ['nullable', 'string', 'max:50'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'team_id.exists' => 'El equipo seleccionado no existe.',
            'per_page.max' => 'Se pueden pedir como máximo 100 registros por página.',
        ];
    }
}
