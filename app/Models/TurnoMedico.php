<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro manual del rol de médicos del día.
 *
 * Se usa para médicos visitantes o ajustes puntuales que no tienen un
 * horario mensual generado.
 */
class TurnoMedico extends Model
{
    protected $fillable = [
        'posta_id',
        'medico_nombre',
        'especialidad',
        'turno',
        'consultorio',
        'estado',
    ];

    /** Posta donde atiende el médico. */
    public function posta(): BelongsTo
    {
        return $this->belongsTo(Posta::class);
    }
}
