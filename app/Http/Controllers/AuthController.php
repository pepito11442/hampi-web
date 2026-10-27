<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Acceso administrativo con correo y contraseña.
 *
 * El personal de salud usa su propio formulario en /equipo/login.
 */
class AuthController extends Controller
{
    /** Muestra el formulario de acceso administrativo. */
    public function showLoginForm()
    {
        return view('hampi.auth_login');
    }

    /**
     * Valida las credenciales, aplica rate limiting y abre la sesión.
     */
    public function login(Request $request)
    {
        // Identificador único: email + IP del usuario
        $claveLimite = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        // Si ya superó los 5 intentos, registrar en el LOG y BLOQUEAR con 429
        if (RateLimiter::tooManyAttempts($claveLimite, 5)) {
            Log::warning("Alerta de Seguridad: Posible ataque de fuerza bruta detectado desde la IP {$request->ip()} intentando acceder a la cuenta {$request->email}.");

            $segundos = RateLimiter::availableIn($claveLimite);
            return response()->view('errors.429', [
                'mensaje' => "Demasiados intentos fallidos. Intente nuevamente en {$segundos} segundos."
            ], 429);
        }

        $credenciales = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credenciales)) {
            // Si acertó, limpiamos el contador de intentos
            RateLimiter::clear($claveLimite);

            $user = Auth::user();

            // Este formulario es exclusivo de administración. El personal de
            // salud debe usar su DNI en /equipo/login.
            if (! $user->esAdmin() || ! $user->activo) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Esta cuenta no tiene acceso administrativo. El personal de salud ingresa con su DNI en /equipo/login.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            return redirect('/admin/dashboard');
        }

        // Si falló, sumamos 1 intento al contador (se reinicia en 60 segundos)
        RateLimiter::hit($claveLimite, 60);

        return back()->withErrors([
            'email' => 'El correo o la contraseña no son correctos.'
        ])->onlyInput('email');
    }

    /** Cierra la sesión administrativa y regenera el token CSRF. */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}