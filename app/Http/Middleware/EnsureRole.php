<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Permite el acceso solo a los roles indicados.
     *
     * Uso: ->middleware('role:admin') o ->middleware('role:doctor,enfermero,enfermera')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->is('empleado', 'empleado/*')
                ? redirect()->route('empleado.login')
                : redirect()->route('login');
        }

        // Una cuenta desactivada no debe conservar el acceso aunque ya tenga
        // una sesión iniciada.
        if (! $user->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $request->is('empleado', 'empleado/*')
                ? redirect()->route('empleado.login')->withErrors([
                    'dni' => 'Tu cuenta está inactiva. Contacta a administración.',
                ])
                : redirect()->route('login')->withErrors([
                    'email' => 'Tu cuenta está inactiva. Contacta a administración.',
                ]);
        }

        if (! in_array($user->role, $roles, true)) {
            if ($user->esAdmin()) {
                return redirect()
                    ->route('admin.dashboard')
                    ->with('mensaje', 'Ese panel corresponde al personal administrativo.');
            }

            if ($user->esPersonal()) {
                return redirect()
                    ->route('empleado.panel')
                    ->with('mensaje', 'Ese panel corresponde al personal de salud.');
            }

            abort(403);
        }

        return $next($request);
    }
}
