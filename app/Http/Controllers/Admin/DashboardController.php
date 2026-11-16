<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
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

        $estadisticas = $this->estadisticasCitas();

        return view('hampi.admin_dashboard', compact(
            'vacunas',
            'medicosHoy',
            'turnosAtendiendo',
            'personalCount',
            'horariosMes',
            'estadisticas'
        ));
    }

    /**
     * Indicadores de citas: hoy, próximos días, cancelaciones y reservas
     * en línea, más la serie de los últimos 14 días para el gráfico.
     *
     * @return array<string, mixed>
     */
    private function estadisticasCitas(): array
    {
        $desde = today()->subDays(13);
        $hasta = today()->addDays(7)->endOfDay();

        $citas = Cita::query()
            ->whereBetween('fecha_hora', [$desde, $hasta])
            ->get(['fecha_hora', 'estado', 'origen']);

        $pasadas = $citas->filter(fn (Cita $cita) => $cita->fecha_hora->lt(today()));
        $cerradas = $pasadas->whereIn('estado', ['Atendida', 'Cancelada', 'Confirmada', 'Pendiente']);

        $serie = collect(range(0, 13))->map(function (int $dias) use ($desde, $citas) {
            $dia = $desde->copy()->addDays($dias);
            $delDia = $citas->filter(fn (Cita $cita) => $cita->fecha_hora->isSameDay($dia));

            return [
                'etiqueta' => $dia->locale('es')->isoFormat('dd D'),
                'atendidas' => $delDia->where('estado', 'Atendida')->count(),
                'total' => $delDia->count(),
            ];
        });

        return [
            'hoy' => $citas->filter(fn (Cita $cita) => $cita->fecha_hora->isToday())->count(),
            'proximos7' => $citas->filter(fn (Cita $cita) => $cita->fecha_hora->gt(today()->endOfDay())
                && in_array($cita->estado, ['Confirmada', 'Pendiente'], true))->count(),
            'enLinea' => $citas->where('origen', 'ciudadano')->count(),
            // Citas pasadas que nunca se cerraron = posible inasistencia.
            'sinCerrar' => $pasadas->whereIn('estado', ['Confirmada', 'Pendiente'])->count(),
            'canceladas' => $pasadas->where('estado', 'Cancelada')->count(),
            'tasaAtencion' => $cerradas->count()
                ? (int) round($pasadas->where('estado', 'Atendida')->count() * 100 / $cerradas->count())
                : null,
            'serie' => $serie,
            'maximo' => max(1, $serie->max('total')),
        ];
    }
}
