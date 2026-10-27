<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTurnoRequest;
use App\Http\Requests\Admin\UpdateTurnoEstadoRequest;
use App\Models\TurnoMedico;

/**
 * Rol manual de médicos: altas y cambios de estado del día.
 */
class TurnoController extends Controller
{
    /** Registra un médico visitante en el rol de hoy. */
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

    /** Cambia el estado de atención de un médico del rol. */
    public function updateEstado(UpdateTurnoEstadoRequest $request, $id)
    {
        $turno = TurnoMedico::findOrFail($id);
        $turno->estado = $request->validated('estado');
        $turno->save();

        return redirect('/admin/dashboard')->with('mensaje', 'Estado del médico actualizado.');
    }

    /** Retira un médico del rol de hoy. */
    public function destroy($id)
    {
        $turno = TurnoMedico::findOrFail($id);
        $turno->delete();

        return redirect('/admin/dashboard')->with('mensaje', 'Médico retirado del rol de hoy.');
    }
}
