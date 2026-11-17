<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Agrega cabeceras de seguridad a todas las respuestas.
     *
     * - CSP con nonce: solo se ejecutan los scripts del propio portal.
     * - HSTS en producción: el navegador siempre usa HTTPS.
     * - Evita el incrustado del portal, el "sniffing" MIME y limita el referente.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Debe generarse antes de renderizar la vista para que @vite y los
        // <script nonce> compartan el mismo valor.
        $nonce = Vite::useCspNonce();

        $respuesta = $next($request);

        // El portal nunca se muestra dentro de un <iframe> (clickjacking).
        $respuesta->headers->set('X-Frame-Options', 'DENY');
        $respuesta->headers->set('X-Content-Type-Options', 'nosniff');
        $respuesta->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $respuesta->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // Con "npm run dev" los assets vienen del servidor de Vite (otro origen,
        // a veces reenviado por Codespaces): en ese caso no se aplica CSP.
        $viteEnDesarrollo = app()->environment('local') && is_file(public_path('hot'));

        if (! $viteEnDesarrollo) {
            $respuesta->headers->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'nonce-{$nonce}'",
                // Sin 'unsafe-inline': las alturas y anchos dinámicos (gráfico,
                // barras de progreso) se aplican desde app.js vía data-*.
                "style-src 'self' 'nonce-{$nonce}'",
                "img-src 'self' data:",
                "font-src 'self'",
                "connect-src 'self'",
                "manifest-src 'self'",
                "worker-src 'self'",
                "frame-ancestors 'none'",
                "form-action 'self'",
                "base-uri 'self'",
                "object-src 'none'",
            ]));
        }

        if ($request->isSecure() && app()->environment('production')) {
            $respuesta->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $respuesta;
    }
}
