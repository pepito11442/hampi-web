<?php

/*
|--------------------------------------------------------------------------
| CORS
|--------------------------------------------------------------------------
|
| Hampi no tiene clientes en otros dominios: el único /api/* lo usa el propio
| formulario "Crear mi código". Sin rutas aquí, Laravel no envía
| Access-Control-Allow-Origin y otros sitios no pueden leer las respuestas.
|
*/

return [

    'paths' => [],

    'allowed_methods' => ['GET'],

    'allowed_origins' => [],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
