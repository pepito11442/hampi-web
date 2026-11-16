<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Medición temporal: registra el tiempo total y las consultas SQL de cada
 * petición. Se activa con LOG_PERF=true y escribe en stderr (canal "perf",
 * visible en Render → Logs aunque LOG_LEVEL sea warning). Marca como posible N+1 cualquier consulta cuya forma
 * (SQL sin valores) se repite 3 veces o más en la misma petición.
 * Con LOG_PERF=sql también lista cada consulta.
 */
class MedirPeticion
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.log_perf')) {
            return $next($request);
        }

        $inicio = microtime(true);
        $consultas = [];

        DB::listen(function (QueryExecuted $q) use (&$consultas) {
            $consultas[] = [$q->sql, $q->time];
        });

        $respuesta = $next($request);

        $repetidas = collect($consultas)
            ->countBy(fn ($c) => $c[0])
            ->filter(fn ($n) => $n >= 3)
            ->sortDesc()
            ->map(fn ($n, $sql) => $n.'× '.mb_strimwidth($sql, 0, 160, '…'))
            ->values()
            ->all();

        Log::channel('perf')->info('perf', [
            'ruta' => $request->method().' /'.ltrim($request->path(), '/'),
            'estado' => $respuesta->getStatusCode(),
            'especulativa' => SolicitudEspeculativa::es($request),
            'ms' => round((microtime(true) - $inicio) * 1000, 1),
            'consultas' => count($consultas),
            'ms_sql' => round(array_sum(array_column($consultas, 1)), 1),
            'n_mas_1' => $repetidas,
        ] + (config('app.log_perf') === 'sql'
            ? ['sql' => array_map(fn ($c) => mb_strimwidth($c[0], 0, 200, '…'), $consultas)]
            : []));

        return $respuesta;
    }
}
