<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    // Relación con la Posta
    public function posta()
    {
        return $this->belongsTo(Posta::class);
    }
}
