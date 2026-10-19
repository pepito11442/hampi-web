<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
{
    $response = $next($request);

    $response->headers->set('X-Frame-Options', 'SAMEORIGIN'); // Evita que tu web sea incrustada en iframes maliciosos (Clickjacking)
    $response->headers->set('X-Content-Type-Options', 'nosniff'); // Evita que el navegador interprete archivos con tipos MIME incorrectos
    $response->headers->set('X-XSS-Protection', '1; mode=block'); // Filtro XSS nativo del navegador
    $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

    return $response;
}
}
