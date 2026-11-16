<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

/**
 * Persona que accede al portal con su DNI y un código de acceso.
 *
 * El DNI identifica a la persona, pero no la autentica: para ver o pedir
 * citas también debe ingresar su código de 6 dígitos. Ese código lo crea
 * la propia persona al registrarse o se lo entrega el personal de la posta.
 */
class Ciudadano extends Model
{
    /** Longitud del código de acceso. */
    public const LONGITUD_CODIGO = 6;

    protected $guarded = ['codigo_acceso', 'codigo_generado_at'];

    protected $hidden = ['codigo_acceso'];

    protected function casts(): array
    {
        return [
            'codigo_generado_at' => 'datetime',
        ];
    }

    /** Citas del ciudadano. */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }

    /** Indica si la ficha ya tiene un código para entrar al portal. */
    public function tieneCodigo(): bool
    {
        return ! empty($this->codigo_acceso);
    }

    /** Comprueba el código ingresado contra el hash guardado. */
    public function verificarCodigo(string $codigo): bool
    {
        return $this->tieneCodigo() && Hash::check($codigo, $this->codigo_acceso);
    }

    /** Guarda un código elegido por la persona. */
    public function asignarCodigo(string $codigo): void
    {
        $this->forceFill([
            'codigo_acceso' => Hash::make($codigo),
            'codigo_generado_at' => now(),
        ])->save();
    }

    /**
     * Genera un código nuevo al azar, invalida el anterior y lo devuelve
     * en claro una sola vez para entregarlo al paciente.
     */
    public function generarCodigo(): string
    {
        $codigo = str_pad((string) random_int(0, 999999), self::LONGITUD_CODIGO, '0', STR_PAD_LEFT);
        $this->asignarCodigo($codigo);

        return $codigo;
    }

    /** Nombre completo para mostrar. */
    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombres.' '.$this->apellidos);
    }
}
