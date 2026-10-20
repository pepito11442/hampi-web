<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTurnoRequest;
use App\Http\Requests\Admin\UpdateTurnoEstadoRequest;
use App\Models\TurnoMedico;

class TurnoController extends Controller
{
    public function store(StoreTurnoRequest $request)
    {
        TurnoMedico::create([
            'posta_id' => 1,
            'medico_nombre' => $request->validated('medico_nombre'),
            'especialidad' => $request->validated('especialidad'),
            'turno' => $request->validated('turno'),
            'consultorio' => $request->validated('consultorio'),
            'estado' => 'Atendiendo',
        ]);

        return redirect('/admin/dashboard')->with('mensaje', 'Médico registrado exitosamente en el rol de hoy.');
    }

    public function updateEstado(UpdateTurnoEstadoRequest $request, $id)
    {
        $turno = TurnoMedico::findOrFail($id);
        $turno->estado = $request->validated('estado');
        $turno->save();

        return redirect('/admin/dashboard')->with('mensaje', 'Estado del médico actualizado.');
    }

    public function destroy($id)
    {
        $turno = TurnoMedico::findOrFail($id);
        $turno->delete();

        return redirect('/admin/dashboard')->with('mensaje', 'Médico retirado del rol de hoy.');
    }
}
