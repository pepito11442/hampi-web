<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Turno mensual asignado a un trabajador para una fecha concreta.
 *
 * El generador mensual crea un registro por día hábil y sábado; los
 * domingos no tienen atención.
 */
class Horario extends Model
{
    /** Definición única de los turnos disponibles. */
    public const TURNOS = [
        'manana' => [
            'label' => 'Mañana',
            'inicio' => '07:00',
            'fin' => '13:00',
            'horas' => 6.0,
        ],
        'tarde' => [
            'label' => 'Tarde',
            'inicio' => '13:00',
            'fin' => '19:30',
            'horas' => 6.5,
        ],
        'completo' => [
            'label' => 'Todo el día',
            'inicio' => '07:00',
            'fin' => '19:00',
            'horas' => 12.0,
        ],
        'sabado' => [
            'label' => 'Sábado',
            'inicio' => '07:00',
            'fin' => '15:00',
            'horas' => 8.0,
        ],
    ];

    protected $fillable = [
        'user_id',
        'fecha',
        'turno',
        'hora_inicio',
        'hora_fin',
        'horas',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'horas' => 'float',
        ];
    }

    /** Trabajador al que pertenece el turno. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Etiqueta legible del turno ("Mañana", "Tarde", etc.). */
    public function getTurnoLabelAttribute(): string
    {
        return self::TURNOS[$this->turno]['label'] ?? ucfirst((string) $this->turno);
    }

    /** Hora de inicio en formato HH:MM, sin segundos. */
    public function getHoraInicioAttribute($value): ?string
    {
        return $value ? substr((string) $value, 0, 5) : null;
    }

    /** Hora de fin en formato HH:MM, sin segundos. */
    public function getHoraFinAttribute($value): ?string
    {
        return $value ? substr((string) $value, 0, 5) : null;
    }
}
