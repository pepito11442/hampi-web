<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ciudadano;
use App\Support\Auditoria;
use Illuminate\Http\Request;

/**
 * Entrega de códigos de acceso del portal ciudadano en ventanilla.
 *
 * Admisión verifica el DNI físico de la persona y le genera un código
 * nuevo; el anterior deja de funcionar.
 */
class CiudadanoController extends Controller
{
    public function codigo(Request $request)
    {
        $datos = $request->validate([
            'dni' => ['required', 'digits:8'],
        ], [
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos.',
        ]);

        $ciudadano = Ciudadano::where('dni', $datos['dni'])->first();

        if (! $ciudadano) {
            return back()->withErrors([
                'dni_codigo' => 'No hay una ficha con ese DNI. La persona puede crear su código desde el portal.',
            ])->withInput();
        }

        $codigo = $ciudadano->generarCodigo();

        Auditoria::registrar('codigo.generado', [
            'ciudadano_afectado' => $ciudadano->id,
            'origen' => 'ventanilla',
        ]);

        return back()->with('codigo_ciudadano', [
            'dni' => $ciudadano->dni,
            'nombre' => $ciudadano->nombre_completo,
            'codigo' => $codigo,
        ]);
    }
}
