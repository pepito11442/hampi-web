<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
        ];
    }

    public function ciudadano()
    {
        return $this->belongsTo(Ciudadano::class);
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function posta()
    {
        return $this->belongsTo(Posta::class);
    }
}
