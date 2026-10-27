<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Cita médica de un ciudadano con un profesional y una posta.
 *
 * El estado determina si la cita ocupa un bloque de la agenda: solo
 * "Confirmada" y "Pendiente" reservan horario.
 */
class Cita extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
        ];
    }

    /** Ciudadano que reservó la cita. */
    public function ciudadano(): BelongsTo
    {
        return $this->belongsTo(Ciudadano::class);
    }

    /** Médico que atiende la cita; puede ser null en citas históricas. */
    public function medico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Establecimiento donde se atiende la cita. */
    public function posta(): BelongsTo
    {
        return $this->belongsTo(Posta::class);
    }
}
