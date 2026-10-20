<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activa', true);
    }

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
