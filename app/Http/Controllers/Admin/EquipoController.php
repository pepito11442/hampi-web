<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Administración de las cuentas del personal de salud.
 *
 * Solo crea y edita médicos y enfermeros; las cuentas administrativas no
 * se gestionan desde este módulo.
 */
class EquipoController extends Controller
{
    /** Lista el equipo, filtrable por médicos o enfermería. */
    public function index(Request $request)
    {
        $rol = (string) $request->query('rol', 'todos');

        $consulta = User::whereIn('role', User::ROLES_PERSONAL)->orderBy('name');

        if ($rol === 'doctores') {
            $consulta->where('role', User::ROL_DOCTOR);
        } elseif ($rol === 'enfermeros') {
            $consulta->whereIn('role', [User::ROL_ENFERMERO, User::ROL_ENFERMERA]);
        } else {
            $rol = 'todos';
        }

        $equipo = $consulta->get();

        $conteos = [
            'todos' => User::whereIn('role', User::ROLES_PERSONAL)->count(),
            'doctores' => User::where('role', User::ROL_DOCTOR)->count(),
            'enfermeros' => User::whereIn('role', [User::ROL_ENFERMERO, User::ROL_ENFERMERA])->count(),
        ];

        return view('hampi.admin.equipo', compact('equipo', 'rol', 'conteos'));
    }

    /** Crea la cuenta de un trabajador con acceso por DNI. */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'dni' => ['required', 'digits:8', 'unique:users,dni'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'role' => ['required', Rule::in([User::ROL_DOCTOR, User::ROL_ENFERMERO])],
            'especialidad' => ['nullable', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'establecimiento' => ['nullable', 'string', 'max:150'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $datos['password'] = Hash::make($datos['password']);
        $datos['activo'] = true;
        $datos['establecimiento'] = ($datos['establecimiento'] ?? null) ?: 'C.S. San Martín de Socabaya';

        User::create($datos);

        return back()->with('mensaje', 'Cuenta creada. El trabajador ya puede ingresar con su DNI.');
    }

    /** Actualiza los datos, el rol, el estado o la contraseña de un trabajador. */
    public function update(Request $request, User $empleado)
    {
        abort_unless($empleado->esPersonal(), 404);

        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'dni' => ['required', 'digits:8', Rule::unique('users', 'dni')->ignore($empleado->id)],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($empleado->id)],
            'role' => ['required', Rule::in([User::ROL_DOCTOR, User::ROL_ENFERMERO])],
            'especialidad' => ['nullable', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'establecimiento' => ['nullable', 'string', 'max:150'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (! empty($datos['password'])) {
            $datos['password'] = Hash::make($datos['password']);
        } else {
            unset($datos['password']);
        }

        $datos['activo'] = $request->boolean('activo');
        $datos['establecimiento'] = ($datos['establecimiento'] ?? null) ?: $empleado->establecimiento;

        $empleado->update($datos);

        return back()->with('mensaje', 'Datos del trabajador actualizados.');
    }

    /** Elimina la cuenta del trabajador y su foto de perfil. */
    public function destroy(User $empleado)
    {
        abort_unless($empleado->esPersonal(), 404);

        if ($empleado->foto_perfil) {
            Storage::disk('public')->delete($empleado->foto_perfil);
        }

        $empleado->delete();

        return back()->with('mensaje', 'Cuenta del trabajador eliminada junto con su historial de horarios.');
    }
}
