{{--
    Navegación instantánea (Speculation Rules, Chrome/Edge; otros navegadores lo ignoran).

    - Páginas públicas (con caché en el servidor): prerender al pasar el mouse ~200 ms.
    - Resto del sitio (paneles): solo prefetch del HTML al pasar el mouse.
    - Nunca: descargas (.ics), fotos, API, enlaces de restablecer contraseña,
      otros dominios, enlaces con target/download o marcados con data-no-prefetch.
      Cerrar sesión y demás acciones son formularios POST: las reglas solo miran enlaces.

    Las respuestas especulativas no modifican la sesión (ver SolicitudEspeculativa).
--}}
@php
    $publicas = ['/', '/medicos-hoy', '/pizarrin-vacunas', '/directorio-postas', '/campanas-preventivas'];
    $excluidas = [
        '/fotos/*', '/storage/*', '/api/*', '/up', '/build/*',
        '/mi-panel/citas/*', '/equipo/restablecer/*', '/*.ics', '/*.webmanifest', '/sw.js',
    ];
    $noEnlaces = ['not' => ['selector_matches' => '[data-no-prefetch], [download], [target], [rel~="nofollow"]']];

    $reglas = [
        'prerender' => [[
            'source' => 'document',
            'where' => ['and' => [
                ['href_matches' => $publicas],
                $noEnlaces,
            ]],
            'eagerness' => 'moderate',
        ]],
        'prefetch' => [[
            'source' => 'document',
            'where' => ['and' => [
                ['href_matches' => '/*'],
                ['not' => ['href_matches' => array_merge($publicas, $excluidas)]],
                $noEnlaces,
            ]],
            'eagerness' => 'moderate',
        ]],
    ];
@endphp
<script type="speculationrules" nonce="{{ Vite::cspNonce() }}">{!! json_encode($reglas, JSON_UNESCAPED_SLASHES) !!}</script>
