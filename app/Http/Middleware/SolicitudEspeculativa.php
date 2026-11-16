<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Las precargas de Speculation Rules (cabecera "Sec-Purpose: prefetch" o
 * "prefetch;prerender") no deben cambiar la sesión.
 *
 * Con SESSION_DRIVER=cookie cada respuesta reescribe la sesión entera en la
 * cookie. Si una precarga tardía llegara después de, por ejemplo, iniciar
 * sesión, pisaría la cookie nueva con el estado anterior. Por eso a estas
 * respuestas se les quitan las cookies: la siguiente petición normal
 * renueva la sesión como siempre.
 */
class SolicitudEspeculativa
{
    public function handle(Request $request, Closure $next): Response
    {
        $respuesta = $next($request);

        if (self::es($request)) {
            foreach ($respuesta->headers->getCookies() as $cookie) {
                $respuesta->headers->removeCookie($cookie->getName(), $cookie->getPath(), $cookie->getDomain());
            }
        }

        return $respuesta;
    }

    public static function es(Request $request): bool
    {
        $proposito = strtolower((string) ($request->headers->get('Sec-Purpose') ?? $request->headers->get('Purpose')));

        return str_contains($proposito, 'prefetch');
    }
}
