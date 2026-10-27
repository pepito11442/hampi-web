<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida el alta de un médico en el rol manual del día.
 */
class StoreTurnoRequest extends FormRequest
{
    /** Solo administración puede modificar el rol. */
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
