<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use App\Models\User;
use App\Services\HorarioGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    public function index(Request $request)
    {
        $rol = (string) $request->query('rol', 'todos');

        $query = User::whereIn('role', User::ROLES_PERSONAL)->orderBy('name');

        if ($rol === 'doctores') {
            $query->where('role', User::ROL_DOCTOR);
        } elseif ($rol === 'enfermeros') {
            $query->whereIn('role', [User::ROL_ENFERMERO, User::ROL_ENFERMERA]);
        } else {
            $rol = 'todos';
        }

        $personal = $query->get();

        $conteos = [
            'todos' => User::whereIn('role', User::ROLES_PERSONAL)->count(),
            'doctores' => User::where('role', User::ROL_DOCTOR)->count(),
            'enfermeros' => User::whereIn('role', [User::ROL_ENFERMERO, User::ROL_ENFERMERA])->count(),
        ];

        $user = null;

        if ($request->filled('user_id')) {
            $user = $personal->firstWhere('id', (int) $request->query('user_id'));
        }

        $user ??= $personal->first();

        $anio = max(2020, min(2100, (int) $request->query('anio', now()->year)));
        $mes = max(1, min(12, (int) $request->query('mes', now()->month)));

        $horarios = collect();
        $totalHoras = 0.0;

        if ($user) {
            $inicio = Carbon::create($anio, $mes, 1);

            $horarios = Horario::where('user_id', $user->id)
                ->whereBetween('fecha', [
                    $inicio->toDateString(),
                    $inicio->copy()->endOfMonth()->toDateString(),
                ])
                ->orderBy('fecha')
                ->get()
                ->keyBy(fn (Horario $horario) => $horario->fecha->format('Y-m-d'));

            $totalHoras = (float) $horarios->sum('horas');
        }

        return view('hampi.admin.horarios', compact(
            'personal',
            'user',
            'anio',
            'mes',
            'horarios',
            'totalHoras',
            'rol',
            'conteos',
        ));
    }

    public function generar(Request $request, HorarioGenerator $generador)
    {
        $datos = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'anio' => ['required', 'integer', 'min:2020', 'max:2100'],
            'mes' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $user = User::findOrFail($datos['user_id']);

        abort_unless($user->esPersonal(), 404);

        try {
            $generador->generar($user, (int) $datos['anio'], (int) $datos['mes']);
        } catch (\Throwable $exception) {
            return back()->withErrors([
                'horario' => 'No se pudo generar el horario: '.$exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('admin.horarios.index', [
                'user_id' => $user->id,
                'anio' => $datos['anio'],
                'mes' => $datos['mes'],
            ])
            ->with('mensaje', 'Horario generado: 150 horas mensuales distribuidas en el calendario.');
    }

    public function destroy(User $empleado, int $anio, int $mes)
    {
        abort_unless($empleado->esPersonal(), 404);

        $anio = max(2020, min(2100, $anio));
        $mes = max(1, min(12, $mes));
        $inicio = Carbon::create($anio, $mes, 1);

        Horario::where('user_id', $empleado->id)
            ->whereBetween('fecha', [
                $inicio->toDateString(),
                $inicio->copy()->endOfMonth()->toDateString(),
            ])
            ->delete();

        return redirect()
            ->route('admin.horarios.index', [
                'user_id' => $empleado->id,
                'anio' => $anio,
                'mes' => $mes,
            ])
            ->with('mensaje', 'Horario del mes eliminado.');
    }
}
