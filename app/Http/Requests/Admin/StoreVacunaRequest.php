<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreVacunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:100'],
            'grupo_objetivo' => ['required', 'string', 'max:150'],
            'stock_estado' => ['required', 'in:Disponible,Por agotarse,Agotado'],
            'requisitos' => ['required', 'string'],
            'es_urgente' => ['sometimes', 'boolean'],
        ];
    }
}
