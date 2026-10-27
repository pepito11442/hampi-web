<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida el cambio de estado de un médico del rol.
 */
class UpdateTurnoEstadoRequest extends FormRequest
{
    /** Solo administración puede modificar el rol. */
    public function authorize(): bool
    {
        return $this->user()?->esAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'estado' => ['required', 'in:Atendiendo,Turno Completo,Reprogramado'],
        ];
    }
}
