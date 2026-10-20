<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use App\Models\User;
use App\Models\VacunaAviso;
use App\Services\AgendaMedica;

class DashboardController extends Controller
{
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
