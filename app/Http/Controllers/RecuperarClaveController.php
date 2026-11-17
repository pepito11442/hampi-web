<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Auditoria;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as ReglaClave;

/**
 * Recuperación de contraseña del personal por correo.
 *
 * Se acepta el DNI o el correo. La respuesta es siempre la misma para no
 * revelar qué cuentas existen.
 */
class RecuperarClaveController extends Controller
{
    private const MENSAJE_ENVIADO = 'Si los datos corresponden a una cuenta activa, enviamos un enlace a su correo. Revisa también la carpeta de spam. El enlace vence en 60 minutos.';

    public function create()
    {
        return view('empleado.olvide_clave');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'identificador' => ['required', 'string', 'max:150'],
        ]);

        $identificador = trim($datos['identificador']);

        $usuario = User::query()
            ->where('activo', true)
            ->where(fn ($consulta) => preg_match('/^\d{8}$/', $identificador)
                ? $consulta->where('dni', $identificador)
                : $consulta->where('email', Str::lower($identificador)))
            ->first();

        if ($usuario?->email) {
            try {
                Password::sendResetLink(['email' => $usuario->email]);
            } catch (\Throwable $error) {
                Log::error('No se pudo enviar el correo de recuperación: '.$error->getMessage());
            }
        }

        return back()->with('mensaje', self::MENSAJE_ENVIADO);
    }

    public function edit(Request $request, string $token)
    {
        return view('empleado.restablecer_clave', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', ReglaClave::min(8)->letters()->numbers()],
        ], [
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $estado = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $usuario, string $clave) {
                $usuario->forceFill([
                    'password' => $clave,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($usuario));

                Auditoria::registrar('contrasena.cambiada', [
                    'usuario_afectado' => $usuario->id,
                    'origen' => 'enlace_recuperacion',
                ]);
            }
        );

        if ($estado !== Password::PASSWORD_RESET) {
            return back()->withErrors([
                'email' => 'El enlace no es válido o ya venció. Solicita uno nuevo.',
            ])->onlyInput('email');
        }

        $usuario = User::where('email', $request->input('email'))->first();
        $destino = $usuario?->esAdmin() ? route('login') : route('empleado.login');

        return redirect($destino)->with('mensaje', 'Tu contraseña se actualizó. Ya puedes ingresar.');
    }
}
