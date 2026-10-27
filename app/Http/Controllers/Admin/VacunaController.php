<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVacunaRequest;
use App\Models\VacunaAviso;

/**
 * Publicación de avisos en el pizarrín de vacunas.
 */
class VacunaController extends Controller
{
    /** Publica un aviso con stock, grupo objetivo y requisitos. */
    public function store(StoreVacunaRequest $request)
    {
        VacunaAviso::create([
            'posta_id' => 1,
            'titulo' => strip_tags($request->validated('titulo')),
            'grupo_objetivo' => strip_tags($request->validated('grupo_objetivo')),
            'stock_estado' => $request->validated('stock_estado'),
            'requisitos' => strip_tags($request->validated('requisitos')),
            'es_urgente' => $request->has('es_urgente'),
        ]);

        return redirect('/admin/dashboard')->with('mensaje', 'Aviso publicado en el Pizarrín exitosamente.');
    }

    /** Retira un aviso del pizarrín. */
    public function destroy($id)
    {
        $vacuna = VacunaAviso::findOrFail($id);
        $vacuna->delete();

        return redirect('/admin/dashboard')->with('mensaje', 'Aviso de vacuna retirado del Pizarrín exitosamente.');
    }
}
