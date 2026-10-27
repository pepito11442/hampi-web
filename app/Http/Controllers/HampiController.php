<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Posta;
use App\Models\Ciudadano;
use App\Models\VacunaAviso;
use App\Models\Cita;
use App\Models\CampanaPreventiva;
use App\Services\AgendaMedica;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;

/**
 * Portal público: portada, acceso ciudadano y páginas informativas.
 *
 * La ciudadanía no crea cuenta; se identifica con su DNI y consulta sus
 * citas y la información publicada por la microred.
 */
class HampiController extends Controller
{
    /** Portada con indicadores del día y acceso por DNI. */
    public function index(AgendaMedica $agenda)
    {
        // Limpiar cualquier sesión anterior
        Session::forget(['ciudadano_dni', 'ciudadano_id']);

        $medicosHoy = $agenda->delDia();

        // Indicadores reales para la portada (sin datos inventados)
        $stats = [
            'turnos' => $agenda->total($medicosHoy),
            'vacunas' => VacunaAviso::where('stock_estado', 'Disponible')->count(),
            'postas' => Posta::where('estado_activo', true)->count(),
        ];

        $proximaCampana = CampanaPreventiva::activas()
            ->orderBy('fecha_inicio')
            ->get()
            ->first(fn (CampanaPreventiva $campana) => $campana->estado !== 'Finalizada');

        return view('hampi.login', compact('stats', 'proximaCampana'));
    }

    /**
     * Registra o actualiza la ficha del ciudadano y abre su sesión.
     *
     * RENIEC es opcional: si no responde, se conservan los nombres que ya
     * existan o los que el usuario escribió manualmente.
     */
    public function accesoPorDni(Request $request)
    {
        $dni = $request->input('dni', '72345678');
        $nombres = trim(strip_tags((string) $request->input('nombres_reniec', '')));
        $apellidos = trim(strip_tags((string) $request->input('apellidos_reniec', '')));

        $ciudadano = Ciudadano::firstOrNew(['dni' => $dni]);

        // Si la ficha ya existe (por ejemplo, creada por un médico), no la
        // sobrescribimos con los valores de respaldo cuando RENIEC no responde.
        if (! $ciudadano->exists) {
            $ciudadano->nombres = $nombres !== '' ? $nombres : 'Ciudadano(a)';
            $ciudadano->apellidos = $apellidos !== '' ? $apellidos : 'de Socabaya';
        } else {
            if ($nombres !== '') {
                $ciudadano->nombres = $nombres;
            }

            if ($apellidos !== '') {
                $ciudadano->apellidos = $apellidos;
            }
        }

        $ciudadano->save();

        Session::put('ciudadano_dni', $ciudadano->dni);

        return redirect()->route('ciudadano.panel');
    }

    /** Panel del ciudadano con sus próximas citas e historial. */
    public function panelCiudadano()
    {
        $dni = Session::get('ciudadano_dni');

        if (! $dni) {
            return redirect('/#acceso');
        }

        $ciudadano = Ciudadano::where('dni', $dni)->first();

        if (! $ciudadano) {
            Session::forget('ciudadano_dni');

            return redirect('/#acceso');
        }

        $citas = Cita::with(['posta', 'medico'])
            ->where('ciudadano_id', $ciudadano->id)
            ->orderBy('fecha_hora')
            ->get();

        return view('hampi.panel_paciente', compact('ciudadano', 'citas'));
    }

    /** Rol de médicos del día, separado por turno. */
    public function verMedicos(AgendaMedica $agenda)
    {
        $medicos = $agenda->delDia();
        $turnosManana = $agenda->manana($medicos);
        $turnosTarde = $agenda->tarde($medicos);

        return view('hampi.medicos', compact('medicos', 'turnosManana', 'turnosTarde'));
    }

    /** Pizarrín público de vacunas y disponibilidad. */
    public function verPizarrin()
    {
        $vacunas = VacunaAviso::with('posta')->orderBy('es_urgente', 'desc')->get();
        return view('hampi.pizarrin', compact('vacunas'));
    }

    /** Directorio de establecimientos activos de la microred. */
    public function verPostas()
    {
        $postas = Posta::where('estado_activo', true)->get();
        return view('hampi.postas', compact('postas'));
    }

    /** Campañas preventivas activas publicadas por la microred. */
    public function verCampanasPreventivas()
    {
        $campanas = CampanaPreventiva::activas()
            ->orderBy('fecha_inicio')
            ->get();

        return view('hampi.campanas', compact('campanas'));
    }

    /** Cierra la sesión ciudadana sin afectar al personal autenticado. */
    public function logoutCiudadano()
    {
        Session::forget(['ciudadano_dni', 'ciudadano_id']);
        return redirect('/');
    }

    /**
     * Consulta los nombres del DNI en RENIEC.
     *
     * Es un servicio opcional: si no hay token o la API falla, el
     * formulario permite completar los nombres manualmente.
     */
    public function consultarDniReniec($dni)
    {
        if (!preg_match('/^[0-9]{8}$/', $dni)) {
            return response()->json(['success' => false, 'mensaje' => 'El DNI debe tener 8 dígitos'], 400);
        }

        $token = config('services.reniec.token');

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Referer' => 'https://apis.net.pe/consulta-dni-api',
                'User-Agent' => 'laravel/framework',
            ])->timeout(5)->get(config('services.reniec.url'), ['numero' => $dni]);

            if ($response->successful()) {
                $data = $response->json();
                
                return response()->json([
                    'success' => true,
                    'nombres' => $data['nombres'],
                    'apellidos' => ($data['apellidoPaterno'] ?? '') . ' ' . ($data['apellidoMaterno'] ?? ''),
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
