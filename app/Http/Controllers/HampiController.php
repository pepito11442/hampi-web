<?php
// app/Http/Controllers/HampiController.php

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

class HampiController extends Controller
{
    // Pantalla 1: Acceso / Inicio
    public function index(AgendaMedica $agenda)
    {
        // Limpiar cualquier sesión anterior
        Session::forget(['ciudadano_dni', 'ciudadano_id']);

        $medicosHoy = $agenda->delDia();

        // Indicadores reales para la portada (sin datos inventados)
        $stats = [
            'turnos' => $agenda->total($medicosHoy),
            'vacunas' => VacunaAviso::where('stock_estado', 'Disponible')->count(),
        ];

        return view('hampi.login', compact('stats'));
    }

    // Validación e ingreso con DNI (Sin redirección)
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

        $citas = Cita::with(['posta', 'medico'])
            ->where('ciudadano_id', $ciudadano->id)
            ->orderBy('fecha_hora')
            ->get();

        return view('hampi.panel_paciente', compact('ciudadano', 'citas'));
    }

    // Pantalla 2: Panel del Paciente
    public function panelCiudadano()
    {
        $dni = Session::get('ciudadano_dni', '72345678');

        $ciudadano = Ciudadano::where('dni', $dni)->first();

        if ($ciudadano) {
            $citas = Cita::with(['posta', 'medico'])
                ->where('ciudadano_id', $ciudadano->id)
                ->orderBy('fecha_hora')
                ->get();
        } else {
            $ciudadano = (object)[
                'nombres' => 'Rosa Elena',
                'apellidos' => 'Quispe Mamani',
                'dni' => $dni
            ];
            $citas = collect();
        }

        return view('hampi.panel_paciente', compact('ciudadano', 'citas'));
    }

    // Pantalla 3: Médicos y Turnos del Día
    public function verMedicos(AgendaMedica $agenda)
    {
        $medicos = $agenda->delDia();
        $turnosManana = $agenda->manana($medicos);
        $turnosTarde = $agenda->tarde($medicos);

        return view('hampi.medicos', compact('medicos', 'turnosManana', 'turnosTarde'));
    }

    // Pantalla 4: El Pizarrín Digital de Vacunas
    public function verPizarrin()
    {
        $vacunas = VacunaAviso::with('posta')->orderBy('es_urgente', 'desc')->get();
        return view('hampi.pizarrin', compact('vacunas'));
    }

    // Pantalla 5: Directorio de Postas de Socabaya
    public function verPostas()
    {
        $postas = Posta::where('estado_activo', true)->get();
        return view('hampi.postas', compact('postas'));
    }

    // Pantalla 6: Campañas preventivas de la Microred
    public function verCampanasPreventivas()
    {
        $campanas = CampanaPreventiva::activas()
            ->orderBy('fecha_inicio')
            ->get();

        return view('hampi.campanas', compact('campanas'));
    }

    public function logoutCiudadano()
    {
        Session::forget(['ciudadano_dni', 'ciudadano_id']);
        return redirect('/');
    }

    // Consulta en vivo a RENIEC
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
