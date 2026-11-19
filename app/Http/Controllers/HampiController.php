<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Posta;
use App\Models\VacunaAviso;
use App\Models\CampanaPreventiva;
use App\Services\AgendaMedica;
use App\Support\Auditoria;
use App\Support\CachePortal;

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
}
