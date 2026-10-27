<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Acceso y panel del personal de salud (médicos y enfermería).
 *
 * El ingreso es con DNI + contraseña; cada trabajador solo ve su propio
 * horario y puede actualizar su perfil profesional.
 */
class EmpleadoController extends Controller
{
    /** Muestra el formulario de acceso del personal. */
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->esPersonal()) {
            return redirect()->route('empleado.panel');
        }

        return view('empleado.login');
    }

    /**
     * Autentica al personal con DNI y contraseña.
     *
     * Aplica el mismo límite de intentos que el acceso administrativo y
     * rechaza cuentas inactivas o con rol ciudadano.
     */
    public function login(Request $request)
    {
        $throttleKey = 'empleado|'.Str::lower((string) $request->input('dni')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            Log::warning("Intento de fuerza bruta en panel de empleados desde {$request->ip()} con DNI {$request->input('dni')}.");

            $segundos = RateLimiter::availableIn($throttleKey);

            return response()->view('errors.429', [
                'mensaje' => "Demasiados intentos fallidos. Intente nuevamente en {$segundos} segundos.",
            ], 429);
        }

        $credenciales = $request->validate([
            'dni' => ['required', 'digits:8'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('dni', $credenciales['dni'])->first();

        $puedeIngresar = $user
            && $user->activo
            && $user->esPersonal()
            && Hash::check($credenciales['password'], $user->password);

        if (! $puedeIngresar) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors([
                'dni' => 'El DNI o la contraseña no son correctos.',
            ])->onlyInput('dni');
        }

        Auth::login($user, $request->boolean('remember'));
        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->route('empleado.panel');
    }

    /** Cierra la sesión del personal y regenera el token CSRF. */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('mensaje', 'Sesión cerrada correctamente.');
    }

    /** Muestra el horario del mes y el perfil del trabajador autenticado. */
    public function panel(Request $request)
    {
        $user = $request->user();

        [$anio, $mes] = $this->periodo($request);
        $horarios = $this->horariosDelMes($user, $anio, $mes);
        $totalHoras = (float) $horarios->sum('horas');

        return view('empleado.panel', compact('user', 'horarios', 'totalHoras', 'anio', 'mes'));
    }

    /**
     * Actualiza los datos profesionales y la foto de perfil.
     *
     * La foto reemplaza a la anterior en el disco público. El nombre, DNI
     * y rol no se pueden cambiar desde aquí.
     */
    public function updatePerfil(Request $request)
    {
        $user = $request->user();

        $datos = $request->validate([
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'especialidad' => ['nullable', 'string', 'max:120'],
            'biografia' => ['nullable', 'string', 'max:2000'],
            'titulos' => ['nullable', 'string', 'max:2000'],
            'experiencia' => ['nullable', 'string', 'max:2000'],
            'foto_perfil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('foto_perfil')) {
            if ($user->foto_perfil) {
                Storage::disk('public')->delete($user->foto_perfil);
            }

            $datos['foto_perfil'] = $request->file('foto_perfil')->store('perfiles', 'public');
        }

        $user->update($datos);

        [$anio, $mes] = $this->periodo($request);

        return redirect()
            ->route('empleado.panel', ['anio' => $anio, 'mes' => $mes])
            ->with('mensaje', 'Perfil actualizado correctamente.');
    }

    /**
     * Normaliza el año y mes solicitados a un rango válido.
     *
     * @return array{0: int, 1: int}
     */
    private function periodo(Request $request): array
    {
        $anio = (int) $request->input('anio', now()->year);
        $mes = (int) $request->input('mes', now()->month);

        return [
            max(2020, min(2100, $anio)),
            max(1, min(12, $mes)),
        ];
    }

    /** Devuelve los turnos del trabajador para el mes indicado. */
    private function horariosDelMes(User $user, int $anio, int $mes)
    {
        $inicio = Carbon::create($anio, $mes, 1);

        return Horario::where('user_id', $user->id)
            ->whereBetween('fecha', [
                $inicio->toDateString(),
                $inicio->copy()->endOfMonth()->toDateString(),
            ])
            ->orderBy('fecha')
            ->get()
            ->keyBy(fn (Horario $horario) => $horario->fecha->format('Y-m-d'));
    }
}
