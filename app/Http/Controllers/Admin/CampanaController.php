<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampanaPreventiva;
use Illuminate\Http\Request;

class CampanaController extends Controller
{
    public function index()
    {
        $campanas = CampanaPreventiva::orderByDesc('activa')
            ->orderByDesc('fecha_inicio')
            ->get();

        return view('hampi.admin.campanas', compact('campanas'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'titulo' => ['required', 'string', 'max:150'],
            'grupo_objetivo' => ['nullable', 'string', 'max:150'],
            'descripcion' => ['required', 'string', 'max:2000'],
            'lugar' => ['nullable', 'string', 'max:150'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ]);

        $datos['activa'] = $request->boolean('activa');

        CampanaPreventiva::create($datos);

        return back()->with('mensaje', 'Campaña preventiva publicada correctamente.');
    }

    public function toggleEstado(CampanaPreventiva $campana)
    {
        $campana->update(['activa' => ! $campana->activa]);

        return back()->with(
            'mensaje',
            $campana->activa
                ? 'Campaña publicada en el portal.'
                : 'Campaña ocultada del portal.'
        );
    }

    public function destroy(CampanaPreventiva $campana)
    {
        $campana->delete();

        return back()->with('mensaje', 'Campaña preventiva eliminada.');
    }
}
