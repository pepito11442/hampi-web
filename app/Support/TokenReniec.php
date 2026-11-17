<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Token de un solo uso para autocompletar nombres con RENIEC.
 *
 * Se emite al abrir "Crear mi código" y queda en la sesión y en la caché.
 * La sesión vive en una cookie, así que reenviar una cookie vieja podría
 * "revivir" el token: por eso también se marca como usado en la caché,
 * que es del servidor y no se puede reenviar.
 */
class TokenReniec
{
    public const CLAVE_SESION = 'reniec_token';

    /** Minutos que dura un token sin usar. */
    public const MINUTOS = 15;

    public static function emitir(Request $request): string
    {
        $token = Str::random(40);

        $request->session()->put(self::CLAVE_SESION, $token);
        Cache::put(self::claveCache($token), true, now()->addMinutes(self::MINUTOS));

        return $token;
    }

    /** Devuelve true solo la primera vez que se presenta un token vigente. */
    public static function consumir(string $token): bool
    {
        return (bool) Cache::pull(self::claveCache($token), false);
    }

    private static function claveCache(string $token): string
    {
        return 'reniec-token:'.hash('sha256', $token);
    }
}
