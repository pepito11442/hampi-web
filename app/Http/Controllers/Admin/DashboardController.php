<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use App\Models\User;
use App\Models\VacunaAviso;
use App\Services\AgendaMedica;

/**
 * Panel general de administración.
 *
 * Reúne el parte del día, los indicadores de vacunas y el acceso a las
 * secciones de equipo, horarios y campañas.
 */
class DashboardController extends Controller
{
    /** Muestra el resumen operativo del día. */
    public function index(AgendaMedica $agenda)
    {
        $vacunas = VacunaAviso::all();
        $medicosHoy = $agenda->delDia();
        $turnosAtendiendo = $agenda->atendiendo($medicosHoy);
        $personalCount = User::whereIn('role', User::ROLES_PERSONAL)->count();
        $horariosMes = Horario::whereBetween('fecha', [
            now()->startOfMonth()->toDateString(),
            now()->endOfMonth()->toDateString(),
        ])->count();

        return view('hampi.admin_dashboard', compact(
            'vacunas',
            'medicosHoy',
            'turnosAtendiendo',
            'personalCount',
            'horariosMes'
        ));
    }
}
