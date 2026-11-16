<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Ciudadano;
use App\Models\Horario;
use App\Models\Posta;
use App\Models\User;
use App\Services\AgendaCitas;
use App\Support\CachePortal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Agenda de citas del médico autenticado.
 *
 * Cada médico solo puede ver, crear y cerrar sus propias citas. Los
 * bloques se calculan a partir de su horario mensual.
 */
class EmpleadoCitaController extends Controller
{
    /**
     * Citas del médico autenticado, separadas para lectura rápida.
     */
    public function index(Request $request)
    {
        $medico = $request->user();

        $citas = Cita::with(['ciudadano', 'posta'])
            ->where('user_id', $medico->id)
            ->orderBy('fecha_hora')
            ->get();

        $citasHoy = $citas
            ->filter(fn (Cita $cita) => $cita->fecha_hora->isToday())
            ->values();

        $proximas = $citas
            ->filter(fn (Cita $cita) => $cita->fecha_hora->gt(today()->endOfDay())
                && in_array($cita->estado, AgendaCitas::ESTADOS_ACTIVOS, true))
            ->values();

        $historial = $citas
            ->filter(fn (Cita $cita) => ! $cita->fecha_hora->isToday()
                && ($cita->fecha_hora->lt(today())
                    || in_array($cita->estado, ['Atendida', 'Cancelada'], true)))
            ->sortByDesc('fecha_hora')
            ->values();

        return view('empleado.citas.index', compact('medico', 'citasHoy', 'proximas', 'historial'));
    }

    /**
     * Formulario para buscar al paciente y elegir un bloque disponible.
     */
    public function create(Request $request, AgendaCitas $agenda)
    {
        $medico = $request->user();
        $dni = trim((string) $request->query('dni', old('dni', '')));
        $errorDni = null;
        $paciente = null;

        if ($dni !== '' && ! preg_match('/^\d{8}$/', $dni)) {
            $errorDni = 'El DNI debe tener exactamente 8 dígitos.';
        } elseif ($dni !== '') {
            $paciente = Ciudadano::where('dni', $dni)->first();
        }

        [$fecha, $horario, $bloques] = $this->fechaDelFormulario($request->query('fecha', old('fecha', '')), $medico, $agenda);

        if ($bloques === null) {
            $horario = $agenda->horarioDelDia($medico, $fecha);
            $bloques = $agenda->slots($medico, $fecha, $horario);
        }

        // Misma consulta y caché que el directorio público de postas.
        $postas = CachePortal::recordar('postas', fn () => Posta::where('estado_activo', true)->orderBy('nombre')->get());
        $postaPredeterminada = $postas->firstWhere('nombre', $medico->establecimiento)?->id
            ?? $postas->first()?->id;

        return view('empleado.citas.create', compact(
            'medico',
            'dni',
            'errorDni',
            'paciente',
            'fecha',
            'horario',
            'bloques',
            'postas',
            'postaPredeterminada',
        ));
    }

    /**
     * Registra al paciente si es nuevo y reserva el bloque elegido.
     */
    public function store(Request $request, AgendaCitas $agenda)
    {
        $medico = $request->user();

        $datos = $request->validate([
            'dni' => ['required', 'digits:8'],
            'posta_id' => [
                'required',
                'integer',
                Rule::exists('postas', 'id')->where(fn ($consulta) => $consulta->where('estado_activo', true)),
            ],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
            'nombres' => ['nullable', 'string', 'max:120'],
            'apellidos' => ['nullable', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        $ciudadano = Ciudadano::where('dni', $datos['dni'])->first();

        if (! $ciudadano) {
            $request->validate([
                'nombres' => ['required', 'string', 'max:120'],
                'apellidos' => ['required', 'string', 'max:120'],
            ]);
        }

        $fecha = Carbon::parse($datos['fecha'])->startOfDay();

        if (! $agenda->slotValido($medico, $fecha, $datos['hora'])) {
            $mensaje = $agenda->horarioDelDia($medico, $fecha)
                ? 'Elige un bloque de 30 minutos dentro de tu turno y posterior a la hora actual.'
                : 'No tienes un turno asignado para la fecha seleccionada.';

            throw ValidationException::withMessages(['hora' => $mensaje]);
        }

        $fechaHora = $agenda->fechaHora($fecha, $datos['hora']);

        $conflictoMedico = Cita::where('user_id', $medico->id)
            ->where('fecha_hora', $fechaHora)
            ->whereIn('estado', AgendaCitas::ESTADOS_ACTIVOS)
            ->exists();

        if ($conflictoMedico) {
            throw ValidationException::withMessages([
                'hora' => 'Ese bloque ya está ocupado en tu agenda. Elige otro horario.',
            ]);
        }

        $conflictoPaciente = $ciudadano && Cita::where('ciudadano_id', $ciudadano->id)
            ->where('fecha_hora', $fechaHora)
            ->whereIn('estado', AgendaCitas::ESTADOS_ACTIVOS)
            ->exists();

        if ($conflictoPaciente) {
            throw ValidationException::withMessages([
                'hora' => 'El paciente ya tiene una cita activa en ese mismo horario.',
            ]);
        }

        $codigoNuevo = null;

        $cita = DB::transaction(function () use ($ciudadano, $datos, $medico, $fechaHora, &$codigoNuevo) {
            $ciudadano = $ciudadano ?: Ciudadano::create([
                'dni' => $datos['dni'],
                'nombres' => strip_tags($datos['nombres']),
                'apellidos' => strip_tags($datos['apellidos']),
                'telefono' => ! empty($datos['telefono']) ? strip_tags($datos['telefono']) : null,
            ]);

            // Primera vez del paciente: se le entrega un código para el portal.
            if (! $ciudadano->tieneCodigo()) {
                $codigoNuevo = $ciudadano->generarCodigo();
            }

            return Cita::create([
                'ciudadano_id' => $ciudadano->id,
                'user_id' => $medico->id,
                'posta_id' => $datos['posta_id'],
                'especialidad' => $medico->especialidad ?: 'Medicina General',
                'fecha_hora' => $fechaHora,
                'estado' => 'Confirmada',
            ]);
        });

        return redirect()
            ->route('empleado.citas.index')
            ->with('mensaje', 'Cita agendada para '.$cita->ciudadano->nombres.' '.$cita->ciudadano->apellidos.' el '.$fechaHora->locale('es')->isoFormat('ddd D MMM, h:mm a').'.')
            ->with('codigo_ciudadano', $codigoNuevo ? [
                'dni' => $cita->ciudadano->dni,
                'nombre' => $cita->ciudadano->nombre_completo,
                'codigo' => $codigoNuevo,
            ] : null);
    }

    /**
     * Genera un código de acceso nuevo para el paciente de una cita propia.
     *
     * Sirve cuando el paciente olvidó su código o sospecha que otra
     * persona lo conoce: el código anterior deja de funcionar.
     */
    public function nuevoCodigo(Request $request, Cita $cita)
    {
        abort_unless((int) $cita->user_id === (int) $request->user()->id, 404);

        $ciudadano = $cita->ciudadano;
        $codigo = $ciudadano->generarCodigo();

        return redirect()
            ->route('empleado.citas.index')
            ->with('codigo_ciudadano', [
                'dni' => $ciudadano->dni,
                'nombre' => $ciudadano->nombre_completo,
                'codigo' => $codigo,
            ]);
    }

    /**
     * Cierra una cita como atendida o cancelada.
     */
    public function actualizarEstado(Request $request, Cita $cita)
    {
        abort_unless((int) $cita->user_id === (int) $request->user()->id, 404);

        $datos = $request->validate([
            'estado' => ['required', Rule::in(['Atendida', 'Cancelada'])],
        ]);

        if (! in_array($cita->estado, AgendaCitas::ESTADOS_ACTIVOS, true)) {
            return back()->withErrors([
                'estado' => 'Esta cita ya está cerrada y no puede cambiar de estado.',
            ]);
        }

        if ($datos['estado'] === 'Atendida' && $cita->fecha_hora->isFuture()) {
            return back()->withErrors([
                'estado' => 'Solo puedes marcar como atendida una cita cuya hora ya pasó.',
            ]);
        }

        $cita->update(['estado' => $datos['estado']]);

        return redirect()
            ->route('empleado.citas.index')
            ->with('mensaje', 'La cita de '.$cita->ciudadano->nombres.' quedó marcada como '.strtolower($datos['estado']).'.');
    }

    /**
     * Bloques disponibles para el selector dinámico de la vista.
     */
    public function disponibilidad(Request $request, AgendaCitas $agenda)
    {
        $datos = $request->validate([
            'fecha' => ['required', 'date'],
        ]);

        $fecha = Carbon::parse($datos['fecha'])->startOfDay();

        if ($fecha->lt(today())) {
            return response()->json([
                'fecha' => $fecha->toDateString(),
                'slots' => [],
                'mensaje' => 'Selecciona una fecha de hoy en adelante.',
            ]);
        }

        $horario = $agenda->horarioDelDia($request->user(), $fecha);
        $bloques = $agenda->slots($request->user(), $fecha);

        $mensaje = null;

        if (! $horario) {
            $mensaje = 'No tienes un turno asignado para esa fecha.';
        } elseif (empty($bloques)) {
            $mensaje = 'No quedan horarios disponibles para ese día.';
        }

        return response()->json([
            'fecha' => $fecha->toDateString(),
            'turno' => $horario?->turno_label,
            'horario' => $horario
                ? $horario->hora_inicio.' a '.$horario->hora_fin
                : null,
            'slots' => $bloques,
            'mensaje' => $mensaje,
        ]);
    }

    /**
     * Fecha inicial: la indicada, o el primer turno próximo del médico.
     *
     * Devuelve [fecha, horario, bloques]; horario y bloques son null cuando
     * todavía hay que calcularlos para la fecha elegida.
     *
     * @return array{0: Carbon, 1: ?Horario, 2: ?array}
     */
    private function fechaDelFormulario(?string $fecha, User $medico, AgendaCitas $agenda): array
    {
        if ($fecha && Carbon::hasFormat($fecha, 'Y-m-d')) {
            $fechaCarbon = Carbon::createFromFormat('!Y-m-d', $fecha)->startOfDay();

            return [$fechaCarbon->lt(today()) ? today() : $fechaCarbon, null, null];
        }

        $proximosHorarios = Horario::where('user_id', $medico->id)
            ->whereDate('fecha', '>=', today())
            ->orderBy('fecha')
            ->get();

        // Si el turno de hoy ya terminó, abre el formulario en el próximo día
        // que realmente tenga bloques libres (una sola consulta de citas).
        if ($primerDia = $agenda->primerDiaConCupo($medico, $proximosHorarios)) {
            return [$primerDia['fecha'], $primerDia['horario'], $primerDia['bloques']];
        }

        return [$proximosHorarios->last()?->fecha?->copy()->startOfDay() ?? today(), null, null];
    }
}
