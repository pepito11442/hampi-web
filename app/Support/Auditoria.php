<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Registro de acciones sensibles (OWASP A09:2025).
 *
 * Cada línea dice quién hizo qué, sobre qué registro, desde qué IP y cuándo.
 * Va al canal "auditoria" (stderr en Render) en formato JSON, así se puede
 * filtrar en los Logs del servicio. Nunca se registran códigos ni contraseñas.
 */
class Auditoria
{
    /**
     * @param  string  $accion  Ej.: "codigo.generado", "usuario.eliminado".
     * @param  array<string, mixed>  $detalle  ID afectado y datos no sensibles.
     */
    public static function registrar(string $accion, array $detalle = []): void
    {
        $request = request();
        $usuario = $request->user();

        Log::channel('auditoria')->info($accion, [
            'accion' => $accion,
            'usuario_id' => $usuario?->id,
            'rol' => $usuario?->role,
            'ciudadano_id' => $usuario ? null : ($request->hasSession() ? $request->session()->get('ciudadano_id') : null),
            'ip' => $request->ip(),
            'fecha' => now()->toIso8601String(),
            ...$detalle,
        ]);
    }

    /** DNI parcialmente oculto para los registros: 72345678 → 72****78. */
    public static function dniOculto(string $dni): string
    {
        return substr($dni, 0, 2).str_repeat('*', max(strlen($dni) - 4, 0)).substr($dni, -2);
    }
}
