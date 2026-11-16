<?php

namespace App\Http\Middleware;

use App\Models\Ciudadano;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exige una sesión ciudadana válida (DNI + código verificados).
 *
 * Deja la ficha en $request->attributes('ciudadano') para los controladores.
 */
class EnsureCiudadano
{
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->session()->get('ciudadano_id');
        $ciudadano = $id ? Ciudadano::find($id) : null;

        if (! $ciudadano || ! $ciudadano->tieneCodigo()) {
            $request->session()->forget(['ciudadano_id', 'ciudadano_dni']);

            return redirect(route('inicio').'#acceso')
                ->with('aviso', 'Ingresa con tu DNI y tu código de acceso para continuar.');
        }

        $request->attributes->set('ciudadano', $ciudadano);

        return $next($request);
    }
}
