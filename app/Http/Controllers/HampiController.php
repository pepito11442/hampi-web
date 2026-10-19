<?php
// app/Http/Controllers/HampiController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Posta;
use App\Models\Ciudadano;
use App\Models\VacunaAviso;
use App\Models\TurnoMedico;
use App\Models\Cita;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;

class HampiController extends Controller
{
    // Pantalla 1: Acceso / Inicio
    public function index()
    {
        // Limpiar cualquier sesión anterior
        Session::forget(['ciudadano_dni', 'ciudadano_id']);

        // Indicadores reales para la portada (sin datos inventados)
        $stats = [
            'turnos' => TurnoMedico::count(),
            'vacunas' => VacunaAviso::where('stock_estado', 'Disponible')->count(),
            'postas' => Posta::where('estado_activo', true)->count(),
        ];

        $postaPrincipal = Posta::where('estado_activo', true)->first();

        return view('hampi.login', compact('stats', 'postaPrincipal'));
    }

    // Validación e ingreso con DNI (Sin redirección)
    public function accesoPorDni(Request $request)
    {
        $dni = $request->input('dni', '72345678');
        $nombres = $request->input('nombres_reniec', 'Ciudadano(a)');
        $apellidos = $request->input('apellidos_reniec', 'de Socabaya');

        $ciudadano = Ciudadano::updateOrCreate(
            ['dni' => $dni],
            [
                'nombres' => $nombres,
                'apellidos' => $apellidos
            ]
        );

        Session::put('ciudadano_dni', $ciudadano->dni);

        $citas = Cita::with('posta')
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
            $citas = Cita::with('posta')
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
    public function verMedicos()
    {
        $turnosManana = TurnoMedico::with('posta')->where('turno', 'Mañana')->get();
        $turnosTarde = TurnoMedico::with('posta')->where('turno', 'Tarde')->get();

        return view('hampi.medicos', compact('turnosManana', 'turnosTarde'));
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

    public function logoutCiudadano()
    {
        Session::forget(['ciudadano_dni', 'ciudadano_id']);
        return redirect('/');
    }

    // --- Panel de Administración (Enfermería / Admisión) ---
    public function adminDashboard()
    {
        $vacunas = VacunaAviso::all();
        $turnos = TurnoMedico::all();
        return view('hampi.admin_dashboard', compact('vacunas', 'turnos'));
    }

        // Guardar aviso en el Pizarrín
    public function storeVacuna(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:100',
            'grupo_objetivo' => 'required|string|max:150',
            'stock_estado' => 'required|in:Disponible,Por agotarse,Agotado',
            'requisitos' => 'required|string'
        ]);

        VacunaAviso::create([
            'posta_id' => 1,
            'titulo' => strip_tags($request->titulo), // Elimina cualquier etiqueta <script> o <iframe>
            'grupo_objetivo' => strip_tags($request->grupo_objetivo),
            'stock_estado' => $request->stock_estado,
            'requisitos' => strip_tags($request->requisitos),
            'es_urgente' => $request->has('es_urgente')
        ]);

        return redirect('/admin/dashboard')->with('mensaje', 'Aviso publicado en el Pizarrín exitosamente.');
    }

    // Eliminar vacuna del Pizarrín
    public function destroyVacuna($id)
    {
        $vacuna = VacunaAviso::findOrFail($id);
        $vacuna->delete();

        return redirect('/admin/dashboard')->with('mensaje', 'Aviso de vacuna retirado del Pizarrín exitosamente.');
    }

    // Guardar nuevo médico
    public function storeTurno(Request $request)
    {
        $request->validate([
            'medico_nombre' => 'required|string|max:100',
            'especialidad' => 'required|string|max:100',
            'turno' => 'required|in:Mañana,Tarde',
            'consultorio' => 'required|string|max:50',
        ]);

        TurnoMedico::create([
            'posta_id' => 1,
            'medico_nombre' => $request->medico_nombre,
            'especialidad' => $request->especialidad,
            'turno' => $request->turno,
            'consultorio' => $request->consultorio,
            'estado' => 'Atendiendo'
        ]);

        return redirect('/admin/dashboard')->with('mensaje', 'Médico registrado exitosamente en el rol de hoy.');
    }

    // Cambiar estado del médico
    public function updateTurnoEstado(Request $request, $id)
    {
        $turno = TurnoMedico::findOrFail($id);
        $turno->estado = $request->estado;
        $turno->save();

        return redirect('/admin/dashboard')->with('mensaje', 'Estado del médico actualizado.');
    }

    // Eliminar médico del rol
    public function destroyTurno($id)
    {
        $turno = TurnoMedico::findOrFail($id);
        $turno->delete();

        return redirect('/admin/dashboard')->with('mensaje', 'Médico retirado del rol de hoy.');
    }

    // Consulta en vivo a RENIEC
    public function consultarDniReniec($dni)
    {
        if (!preg_match('/^[0-9]{8}$/', $dni)) {
            return response()->json(['success' => false, 'mensaje' => 'El DNI debe tener 8 dígitos'], 400);
        }

        $token = env('RENIEC_API_TOKEN', '');

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Referer' => 'https://apis.net.pe/consulta-dni-api',
                'User-Agent' => 'laravel/framework',
            ])->timeout(5)->get("https://api.apis.net.pe/v2/reniec/dni?numero={$dni}");

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
        // Eliminar vacuna o aviso del Pizarrín

}