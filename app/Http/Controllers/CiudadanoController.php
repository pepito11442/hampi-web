<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Ciudadano;
use App\Models\Posta;
use App\Models\User;
use App\Services\AgendaCitas;
use App\Services\CalendarioIcs;
use App\Support\Auditoria;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Acceso y autoservicio de la ciudadanía.
 *
 * Para entrar se pide DNI + código de acceso de 6 dígitos. El código lo
 * entrega siempre el personal de la posta (ventanilla o médico) tras ver el
 * DNI físico: no hay registro ni recuperación desde internet.
 */
class CiudadanoController extends Controller
{
    /** Intentos fallidos permitidos antes de bloquear temporalmente. */
    private const MAX_INTENTOS = 5;

    /** Segundos de bloqueo tras superar los intentos. */
    private const BLOQUEO_SEGUNDOS = 300;

    /** Citas activas futuras que una persona puede tener a la vez. */
    public const MAX_CITAS_ACTIVAS = 3;

    /** Días hacia adelante que se pueden reservar. */
    public const DIAS_RESERVA = 30;


    /* ---------------------------------------------------------------------- */
    /* Acceso                                                                  */
    /* ---------------------------------------------------------------------- */

    /** Verifica DNI + código y abre la sesión ciudadana. */
    public function login(Request $request)
    {
        $datos = $request->validate([
            'dni' => ['required', 'digits:8'],
            'codigo' => ['required', 'digits:'.Ciudadano::LONGITUD_CODIGO],
        ], [
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos.',
            'codigo.digits' => 'El código de acceso tiene 6 dígitos.',
        ]);

        $clave = 'ciudadano:'.$datos['dni'].'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($clave, self::MAX_INTENTOS)) {
            Log::warning("Bloqueo temporal del acceso ciudadano para el DNI {$datos['dni']} desde {$request->ip()}.");
            $minutos = (int) ceil(RateLimiter::availableIn($clave) / 60);

            throw ValidationException::withMessages([
                'codigo' => "Demasiados intentos. Vuelve a intentarlo en {$minutos} minuto(s).",
            ])->redirectTo(route('inicio').'#acceso');
        }

        $ciudadano = Ciudadano::where('dni', $datos['dni'])->first();

        if (! $ciudadano || ! $ciudadano->verificarCodigo($datos['codigo'])) {
            RateLimiter::hit($clave, self::BLOQUEO_SEGUNDOS);

            throw ValidationException::withMessages([
                'codigo' => 'El DNI o el código no son correctos. Si es tu primera vez, crea tu código. Si te atendieron en la posta, pide ahí tu código de acceso.',
            ])->redirectTo(route('inicio').'#acceso');
        }

        RateLimiter::clear($clave);

        // Evita la fijación de sesión al cambiar de identidad.
        $request->session()->regenerate();
        $request->session()->put([
            'ciudadano_id' => $ciudadano->id,
            'ciudadano_dni' => $ciudadano->dni,
        ]);

        return redirect()->route('ciudadano.panel');
    }

    /** Cierra la sesión ciudadana sin afectar al personal autenticado. */
    public function logout(Request $request)
    {
        $request->session()->forget(['ciudadano_id', 'ciudadano_dni']);
        $request->session()->regenerateToken();

        return redirect()->route('inicio')->with('mensaje', 'Cerraste tu sesión.');
    }

    /* ---------------------------------------------------------------------- */
    /* Panel                                                                   */
    /* ---------------------------------------------------------------------- */

    /** Panel con próximas citas e historial. */
    public function panel(Request $request)
    {
        $ciudadano = $this->ciudadano($request);

        $citas = Cita::with(['posta', 'medico'])
            ->where('ciudadano_id', $ciudadano->id)
            ->orderBy('fecha_hora')
            ->get();

        return view('hampi.panel_paciente', compact('ciudadano', 'citas'));
    }

    /* ---------------------------------------------------------------------- */
    /* Autoagendado                                                            */
    /* ---------------------------------------------------------------------- */

    /** Formulario para pedir una cita con un médico que tenga turnos. */
    public function crearCita(Request $request, AgendaCitas $agenda)
    {
        $ciudadano = $this->ciudadano($request);
        $medicos = $this->medicosConTurnos();

        $medico = $medicos->firstWhere('id', (int) $request->query('medico'))
            ?? $medicos->first();

        $fecha = $this->fechaValida($request->query('fecha'));
        $primerDia = $medico && ! $request->filled('fecha')
            ? $this->primerDiaConCupo($medico, $agenda)
            : null;

        if ($primerDia) {
            $fecha = $primerDia['fecha'];
            $bloques = $primerDia['bloques'];
        } else {
            $bloques = $medico ? $agenda->slots($medico, $fecha) : [];
        }
        $citasActivas = $this->citasActivas($ciudadano);

        return view('hampi.ciudadano_cita', compact(
            'ciudadano', 'medicos', 'medico', 'fecha', 'bloques', 'citasActivas'
        ));
    }

    /** Bloques libres de un médico para una fecha (JSON). */
    public function disponibilidad(Request $request, AgendaCitas $agenda)
    {
        $datos = $request->validate([
            'medico' => ['required', 'integer'],
            'fecha' => ['required', 'date_format:Y-m-d'],
        ]);

        $medico = $this->medicosConTurnos()->firstWhere('id', (int) $datos['medico']);
        $fecha = Carbon::createFromFormat('!Y-m-d', $datos['fecha']);

        if (! $medico) {
            return response()->json(['slots' => [], 'mensaje' => 'Ese médico no tiene turnos próximos.']);
        }

        if ($fecha->lt(today()) || $fecha->gt(today()->addDays(self::DIAS_RESERVA))) {
            return response()->json(['slots' => [], 'mensaje' => 'Elige una fecha dentro de los próximos '.self::DIAS_RESERVA.' días.']);
        }

        $horario = $agenda->horarioDelDia($medico, $fecha);
        $bloques = $agenda->slots($medico, $fecha);

        return response()->json([
            'fecha' => $fecha->toDateString(),
            'turno' => $horario?->turno_label,
            'horario' => $horario ? $horario->hora_inicio.' a '.$horario->hora_fin : null,
            'slots' => $bloques,
            'mensaje' => ! $horario
                ? 'El médico no atiende ese día.'
                : (empty($bloques) ? 'Ya no quedan cupos para ese día.' : null),
        ]);
    }

    /** Reserva el bloque elegido por el ciudadano. */
    public function guardarCita(Request $request, AgendaCitas $agenda)
    {
        $ciudadano = $this->ciudadano($request);

        $datos = $request->validate([
            'medico' => ['required', 'integer'],
            'fecha' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:'.today()->addDays(self::DIAS_RESERVA)->toDateString()],
            'hora' => ['required', 'date_format:H:i'],
        ], [
            'fecha.before_or_equal' => 'Solo puedes reservar dentro de los próximos '.self::DIAS_RESERVA.' días.',
        ]);

        $medico = $this->medicosConTurnos()->firstWhere('id', (int) $datos['medico']);

        if (! $medico) {
            throw ValidationException::withMessages(['medico' => 'Elige un médico de la lista.']);
        }

        if ($this->citasActivas($ciudadano) >= self::MAX_CITAS_ACTIVAS) {
            throw ValidationException::withMessages([
                'hora' => 'Ya tienes '.self::MAX_CITAS_ACTIVAS.' citas pendientes. Cancela una o espera a ser atendido para pedir otra.',
            ]);
        }

        $fecha = Carbon::createFromFormat('!Y-m-d', $datos['fecha']);

        if (! $agenda->slotValido($medico, $fecha, $datos['hora'])) {
            throw ValidationException::withMessages(['hora' => 'Ese horario ya no está disponible. Elige otro.']);
        }

        $fechaHora = $agenda->fechaHora($fecha, $datos['hora']);
        $posta = $this->postaDelMedico($medico);

        if (! $posta) {
            throw ValidationException::withMessages(['medico' => 'No hay establecimientos activos para reservar en este momento.']);
        }

        $cita = DB::transaction(function () use ($ciudadano, $medico, $fechaHora, $posta) {
            $ocupado = Cita::where('user_id', $medico->id)
                ->where('fecha_hora', $fechaHora)
                ->whereIn('estado', AgendaCitas::ESTADOS_ACTIVOS)
                ->lockForUpdate()
                ->exists();

            if ($ocupado) {
                throw ValidationException::withMessages(['hora' => 'Alguien acaba de tomar ese horario. Elige otro.']);
            }

            $mismoDia = Cita::where('ciudadano_id', $ciudadano->id)
                ->where('user_id', $medico->id)
                ->whereDate('fecha_hora', $fechaHora->toDateString())
                ->whereIn('estado', AgendaCitas::ESTADOS_ACTIVOS)
                ->exists();

            if ($mismoDia) {
                throw ValidationException::withMessages(['hora' => 'Ya tienes una cita con este médico ese mismo día.']);
            }

            return Cita::create([
                'ciudadano_id' => $ciudadano->id,
                'user_id' => $medico->id,
                'posta_id' => $posta->id,
                'especialidad' => $medico->especialidad ?: 'Medicina General',
                'fecha_hora' => $fechaHora,
                'estado' => 'Pendiente',
                'origen' => 'ciudadano',
            ]);
        });

        return redirect()
            ->route('ciudadano.panel')
            ->with('mensaje', 'Cita reservada con '.$medico->name.' el '.$cita->fecha_hora->locale('es')->isoFormat('dddd D [de] MMMM, HH:mm').'. Llega 15 minutos antes con tu DNI.');
    }

    /** El ciudadano cancela una cita propia que todavía no ocurre. */
    public function cancelarCita(Request $request, Cita $cita)
    {
        $ciudadano = $this->ciudadano($request);

        abort_unless((int) $cita->ciudadano_id === (int) $ciudadano->id, 404);

        if (! in_array($cita->estado, AgendaCitas::ESTADOS_ACTIVOS, true) || $cita->fecha_hora->isPast()) {
            return back()->withErrors(['cita' => 'Esta cita ya no se puede cancelar.']);
        }

        $anterior = $cita->estado;
        $cita->update(['estado' => 'Cancelada']);

        Auditoria::registrar('cita.estado_cambiado', [
            'cita_id' => $cita->id,
            'de' => $anterior,
            'a' => 'Cancelada',
        ]);

        return redirect()->route('ciudadano.panel')->with('mensaje', 'Cancelaste tu cita. El horario quedó libre para otra persona.');
    }

    /** Descarga la cita como evento de calendario (.ics). */
    public function calendario(Request $request, Cita $cita, CalendarioIcs $ics)
    {
        $ciudadano = $this->ciudadano($request);

        abort_unless((int) $cita->ciudadano_id === (int) $ciudadano->id, 404);

        $cita->loadMissing(['posta', 'medico']);

        return response($ics->paraCita($cita), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="cita-hampi-'.$cita->id.'.ics"',
        ]);
    }

    /* ---------------------------------------------------------------------- */
    /* Apoyo                                                                   */
    /* ---------------------------------------------------------------------- */

    private function ciudadano(Request $request): Ciudadano
    {
        return $request->attributes->get('ciudadano');
    }

    private function citasActivas(Ciudadano $ciudadano): int
    {
        return Cita::where('ciudadano_id', $ciudadano->id)
            ->whereIn('estado', AgendaCitas::ESTADOS_ACTIVOS)
            ->where('fecha_hora', '>=', now())
            ->count();
    }

    /** Médicos activos con al menos un turno en el periodo reservable. */
    private function medicosConTurnos()
    {
        return User::query()
            ->where('role', User::ROL_DOCTOR)
            ->where('activo', true)
            ->whereHas('horarios', fn ($consulta) => $consulta->whereBetween('fecha', [
                today()->toDateString(),
                today()->addDays(self::DIAS_RESERVA)->toDateString(),
            ]))
            ->orderBy('name')
            ->get(['id', 'name', 'especialidad', 'establecimiento']);
    }

    /** @return array{fecha: Carbon, horario: \App\Models\Horario, bloques: array}|null */
    private function primerDiaConCupo(User $medico, AgendaCitas $agenda): ?array
    {
        $horarios = $medico->horarios()
            ->whereBetween('fecha', [today()->toDateString(), today()->addDays(self::DIAS_RESERVA)->toDateString()])
            ->orderBy('fecha')
            ->get();

        return $agenda->primerDiaConCupo($medico, $horarios);
    }

    private function fechaValida(?string $fecha): Carbon
    {
        if ($fecha && Carbon::hasFormat($fecha, 'Y-m-d')) {
            $dia = Carbon::createFromFormat('!Y-m-d', $fecha);

            if ($dia->gte(today()) && $dia->lte(today()->addDays(self::DIAS_RESERVA))) {
                return $dia;
            }
        }

        return today();
    }

    private function postaDelMedico(User $medico): ?Posta
    {
        $postas = Posta::where('estado_activo', true)->orderBy('id')->get();

        return $postas->firstWhere('nombre', $medico->establecimiento) ?? $postas->first();
    }
}
