<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Persona que accede al portal con su DNI.
 *
 * No tiene credenciales propias: se identifica con el DNI y puede tener
 * citas asociadas en la microred.
 */
class Ciudadano extends Model
{
    protected $guarded = [];
}
