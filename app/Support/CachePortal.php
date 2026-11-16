<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Caché corta para las páginas públicas más visitadas.
 *
 * Las claves llevan un número de versión: cuando el personal cambia
 * turnos, vacunas, postas o campañas se incrementa la versión y todas las
 * entradas anteriores quedan obsoletas al instante.
 */
class CachePortal
{
    /** Segundos que vive cada entrada (el estado "atendiendo" depende de la hora). */
    public const TTL = 60;

    private const CLAVE_VERSION = 'portal:version';

    public static function recordar(string $clave, Closure $callback, int $ttl = self::TTL): mixed
    {
        return Cache::remember('portal:'.self::version().':'.$clave, $ttl, $callback);
    }

    public static function limpiar(): void
    {
        Cache::forever(self::CLAVE_VERSION, self::version() + 1);
    }

    private static function version(): int
    {
        return (int) Cache::get(self::CLAVE_VERSION, 1);
    }
}
