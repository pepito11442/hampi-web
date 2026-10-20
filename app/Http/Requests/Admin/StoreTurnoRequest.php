<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTurnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'medico_nombre' => ['required', 'string', 'max:100'],
            'especialidad' => ['required', 'string', 'max:100'],
            'turno' => ['required', 'in:Mañana,Tarde'],
            'consultorio' => ['required', 'string', 'max:50'],
        ];
    }
}
