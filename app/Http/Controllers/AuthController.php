<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('hampi.auth_login');
    }

    public function login(Request $request)
    {
        // Identificador único: email + IP del usuario
        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        // Si ya superó los 5 intentos, registrar en el LOG y BLOQUEAR con 429
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            Log::warning("Alerta de Seguridad: Posible ataque de fuerza bruta detectado desde la IP {$request->ip()} intentando acceder a la cuenta {$request->email}.");

            $segundos = RateLimiter::availableIn($throttleKey);
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
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            return redirect('/admin/dashboard');
        }

        // Si falló, sumamos 1 intento al contador (se reinicia en 60 segundos)
        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'El correo o la contraseña no son correctos.'
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}