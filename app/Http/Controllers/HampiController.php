<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Posta;
use App\Models\VacunaAviso;
use App\Models\CampanaPreventiva;
use App\Services\AgendaMedica;
use App\Support\Auditoria;
use App\Support\CachePortal;
use App\Support\TokenReniec;
use Illuminate\Support\Facades\Http;

/**
 * Portal público: portada, acceso ciudadano y páginas informativas.
 *
 * El acceso ciudadano (DNI + código) vive en CiudadanoController.
 */
class HampiController extends Controller
{
    /** Portada con indicadores del día y acceso por DNI. */
    public function index(AgendaMedica $agenda)
    {
        $datos = CachePortal::recordar('portada', function () use ($agenda) {
            return [
                // Indicadores reales para la portada (sin datos inventados)
                'stats' => [
                    'turnos' => $agenda->total($agenda->delDia()),
                    'vacunas' => VacunaAviso::where('stock_estado', 'Disponible')->count(),
                    'postas' => Posta::where('estado_activo', true)->count(),
                ],
                'proximaCampana' => CampanaPreventiva::activas()
                    ->orderBy('fecha_inicio')
                    ->get()
                    ->first(fn (CampanaPreventiva $campana) => $campana->estado !== 'Finalizada'),
            ];
        });

        return view('hampi.login', $datos);
    }

    /** Rol de médicos del día, separado por turno. */
    public function verMedicos(AgendaMedica $agenda)
    {
        $medicos = CachePortal::recordar('agenda-hoy', fn () => $agenda->delDia());
        $turnosManana = $agenda->manana($medicos);
        $turnosTarde = $agenda->tarde($medicos);

        return view('hampi.medicos', compact('medicos', 'turnosManana', 'turnosTarde'));
    }

    /** Pizarrín público de vacunas y disponibilidad. */
    public function verPizarrin()
    {
        $vacunas = CachePortal::recordar('pizarrin', fn () => VacunaAviso::with('posta')->orderBy('es_urgente', 'desc')->get());
        return view('hampi.pizarrin', compact('vacunas'));
    }

    /** Directorio de establecimientos activos de la microred. */
    public function verPostas()
    {
        $postas = CachePortal::recordar('postas', fn () => Posta::where('estado_activo', true)->orderBy('nombre')->get());
        return view('hampi.postas', compact('postas'));
    }

    /** Campañas preventivas activas publicadas por la microred. */
    public function verCampanasPreventivas()
    {
        $campanas = CachePortal::recordar('campanas', fn () => CampanaPreventiva::activas()
            ->orderBy('fecha_inicio')
            ->get());

        return view('hampi.campanas', compact('campanas'));
    }

    /**
     * Consulta los nombres del DNI en RENIEC.
     *
     * Es un servicio opcional: si no hay token o la API falla, el
     * formulario permite completar los nombres manualmente.
     */
    public function consultarDniReniec(Request $request, $dni)
    {
        if (! preg_match('/^[0-9]{8}$/', $dni)) {
            return response()->json(['success' => false, 'mensaje' => 'El DNI debe tener 8 dígitos'], 400);
        }

        // Solo quien abrió "Crear mi código" puede consultar, y una sola vez
        // por formulario: así el endpoint no sirve para enumerar nombres.
        $tokenRecibido = (string) $request->header('X-Reniec-Token', '');
        $tokenSesion = $request->hasSession() ? (string) $request->session()->pull(TokenReniec::CLAVE_SESION, '') : '';
        $valido = $tokenRecibido !== ''
            && hash_equals($tokenSesion, $tokenRecibido)
            && TokenReniec::consumir($tokenRecibido);

        Auditoria::registrar('reniec.consulta', [
            'dni' => Auditoria::dniOculto($dni),
            'permitida' => $valido,
        ]);

        if (! $valido) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Escribe tus nombres manualmente.',
            ], 403);
        }

        $token = config('services.reniec.token');

        if (! $token) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Validación con RENIEC no configurada. Escribe tus nombres manualmente.',
            ], 404);
        }

        try {
            $respuesta = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Referer' => 'https://apis.net.pe/consulta-dni-api',
                'User-Agent' => 'laravel/framework',
            ])->timeout(5)->get(config('services.reniec.url'), ['numero' => $dni]);

            if ($respuesta->successful()) {
                $datos = $respuesta->json();
                
                return response()->json([
                    'success' => true,
                    'nombres' => $datos['nombres'] ?? '',
                    'apellidos' => ($datos['apellidoPaterno'] ?? '') . ' ' . ($datos['apellidoMaterno'] ?? ''),
                    'origen' => 'Padrón RENIEC Oficial'
                ]);
            }
        } catch (\Exception $e) {
            // Error de conexión a la API
        }

        return response()->json([
            'success' => false,
            'mensaje' => 'No se pudo conectar con el servicio de RENIEC. Puedes escribir tus nombres manualmente.'
        ], 404);
    }
}
