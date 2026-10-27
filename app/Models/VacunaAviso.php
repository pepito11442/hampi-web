<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Aviso del pizarrín de vacunas.
 *
 * Publica stock, grupo objetivo y requisitos de una vacuna o campaña de
 * inmunización en un establecimiento.
 */
class VacunaAviso extends Model
{
    protected $guarded = [];

    /** Posta que publica el aviso. */
    public function posta(): BelongsTo
    {
        return $this->belongsTo(Posta::class);
    }
}
