<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTurnoLabelAttribute(): string
    {
        return self::TURNOS[$this->turno]['label'] ?? ucfirst((string) $this->turno);
    }

    public function getHoraInicioAttribute($value): ?string
    {
        return $value ? substr((string) $value, 0, 5) : null;
    }

    public function getHoraFinAttribute($value): ?string
    {
        return $value ? substr((string) $value, 0, 5) : null;
    }
}
