<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Campaña preventiva publicada por la microred.
 *
 * El estado ("En curso", "Próxima", "Finalizada") se calcula a partir de
 * las fechas, no se guarda en la base de datos.
 */
class CampanaPreventiva extends Model
{
    protected $table = 'campanas_preventivas';

    protected $fillable = [
        'titulo',
        'grupo_objetivo',
        'descripcion',
        'lugar',
        'fecha_inicio',
        'fecha_fin',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'activa' => 'boolean',
        ];
    }

    /** Limita la consulta a las campañas publicadas en el portal. */
    public function scopeActivas(Builder $consulta): Builder
    {
        return $consulta->where('activa', true);
    }

    /** Periodo legible para las vistas ("05/10/2026 al 12/10/2026"). */
    public function getPeriodoAttribute(): string
    {
        if (! $this->fecha_inicio && ! $this->fecha_fin) {
            return 'Fechas por confirmar';
        }

        $inicio = $this->fecha_inicio?->format('d/m/Y');
        $fin = $this->fecha_fin?->format('d/m/Y');

        if ($inicio && $fin && $inicio !== $fin) {
            return "{$inicio} al {$fin}";
        }

        return $inicio ?: $fin;
    }

    /** Estado calculado según las fechas de la campaña. */
    public function getEstadoAttribute(): string
    {
        $hoy = Carbon::today();

        if ($this->fecha_fin && $this->fecha_fin->lt($hoy)) {
            return 'Finalizada';
        }

        if ($this->fecha_inicio && $this->fecha_inicio->gt($hoy)) {
            return 'Próxima';
        }

        return 'En curso';
    }
}
