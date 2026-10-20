<?php

/*
|--------------------------------------------------------------------------
| Navegación principal del portal
|--------------------------------------------------------------------------
|
| Fuente única de verdad para los enlaces del portal público. Se usa en la
| barra de escritorio, el menú móvil, el footer y la barra inferior.
|
*/

return [
    [
        'clave' => 'inicio',
        'etiqueta' => 'Inicio',
        'etiqueta_corta' => 'Inicio',
        'href' => '/',
        'patron' => '/',
        'icono' => 'home',
    ],
    [
        'clave' => 'medicos',
        'etiqueta' => 'Médicos',
        'etiqueta_corta' => 'Médicos',
        'href' => '/medicos-hoy',
        'patron' => 'medicos-hoy',
        'icono' => 'stethoscope',
    ],
    [
        'clave' => 'vacunas',
        'etiqueta' => 'Vacunas',
        'etiqueta_corta' => 'Vacunas',
        'href' => '/pizarrin-vacunas',
        'patron' => 'pizarrin-vacunas',
        'icono' => 'syringe',
    ],
    [
        'clave' => 'preventivas',
        'etiqueta' => 'Campañas preventivas',
        'etiqueta_corta' => 'Preventivas',
        'href' => '/campanas-preventivas',
        'patron' => 'campanas-preventivas',
        'icono' => 'shield-check',
    ],
    [
        'clave' => 'postas',
        'etiqueta' => 'Postas',
        'etiqueta_corta' => 'Postas',
        'href' => '/directorio-postas',
        'patron' => 'directorio-postas',
        'icono' => 'map-pin',
    ],
];
