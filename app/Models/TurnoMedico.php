<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TurnoMedico extends Model
{
     protected $guarded = [];
    // Relación con la Posta
    public function posta()
    {
        return $this->belongsTo(Posta::class);
    }
}
