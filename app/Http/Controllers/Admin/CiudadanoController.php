<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ciudadano;
use App\Support\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Entrega de códigos de acceso del portal ciudadano en ventanilla.
 *
 * Admisión verifica el DNI físico de la persona y le genera un código
 * nuevo; el anterior deja de funcionar. Si el DNI aún no tiene ficha, se
 * crea con los nombres que admisión copia del DNI físico.
 */
class CiudadanoController extends Controller
{
    public function codigo(Request $request)
    {
        $datos = $request->validate([
            'dni' => ['required', 'digits:8'],
            'nombres' => ['nullable', 'string', 'max:120'],
            'apellidos' => ['nullable', 'string', 'max:120'],
        ], [
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos.',
        ]);

        $ciudadano = Ciudadano::where('dni', $datos['dni'])->first();
        $nuevo = false;

        if (! $ciudadano) {
            // Persona nueva: admisión copia los nombres del DNI físico.
            if (blank($datos['nombres'] ?? null) || blank($datos['apellidos'] ?? null)) {
                return back()->withErrors([
                    'dni_codigo' => 'Es la primera vez de este DNI: escribe sus nombres y apellidos tal como figuran en el DNI.',
                ])->withInput();
            }

            $ciudadano = Ciudadano::create([
                'dni' => $datos['dni'],
                'nombres' => strip_tags(trim($datos['nombres'])),
                'apellidos' => strip_tags(trim($datos['apellidos'])),
            ]);
            $nuevo = true;
        }

        $codigo = $ciudadano->generarCodigo();

        Auditoria::registrar('codigo.generado', [
            'ciudadano_afectado' => $ciudadano->id,
            'origen' => 'ventanilla',
            'ficha_nueva' => $nuevo,
        ]);

        return back()->with('codigo_ciudadano', [
            'dni' => $ciudadano->dni,
            'nombre' => $ciudadano->nombre_completo,
            'codigo' => $codigo,
        ]);
    }

    /**
     * Nombres de un DNI para autocompletar la ventanilla.
     *
     * Solo lo usa el personal con sesión (admin o admisión). Primero mira
     * la ficha propia; si no existe, consulta RENIEC (apis.net.pe) con un
     * reintento y guarda el resultado 30 días para no gastar la cuota.
     * Si algo falla, admisión escribe los nombres a mano.
     */
    public function reniec(Request $request, string $dni)
    {
        if (! preg_match('/^[0-9]{8}$/', $dni)) {
            return response()->json(['success' => false, 'mensaje' => 'El DNI debe tener 8 dígitos.'], 422);
        }

        $ficha = Ciudadano::where('dni', $dni)->first();

        if ($ficha) {
            return response()->json([
                'success' => true,
                'existe' => true,
                'nombres' => $ficha->nombres,
                'apellidos' => $ficha->apellidos,
            ]);
        }

        $token = config('services.reniec.token');

        if (! $token) {
            return response()->json(['success' => false, 'mensaje' => 'RENIEC no está configurado. Escribe los nombres.'], 503);
        }

        Auditoria::registrar('reniec.consulta', [
            'dni' => Auditoria::dniOculto($dni),
            'permitida' => true,
        ]);

        $datos = Cache::get('reniec:'.$dni);

        if (! $datos) {
            try {
                $respuesta = Http::withHeaders([
                    'Authorization' => 'Bearer '.$token,
                    'Referer' => 'https://apis.net.pe/consulta-dni-api',
                    'User-Agent' => 'laravel/framework',
                ])->timeout(8)->retry(2, 300, throw: false)
                    ->get(config('services.reniec.url'), ['numero' => $dni]);

                if ($respuesta->successful() && filled($respuesta->json('nombres'))) {
                    $datos = [
                        'nombres' => (string) $respuesta->json('nombres'),
                        'apellidos' => trim(($respuesta->json('apellidoPaterno') ?? '').' '.($respuesta->json('apellidoMaterno') ?? '')),
                    ];
                    Cache::put('reniec:'.$dni, $datos, now()->addDays(30));
                }
            } catch (\Throwable $e) {
                $datos = null;
            }
        }

        if (! $datos) {
            return response()->json(['success' => false, 'mensaje' => 'RENIEC no respondió. Escribe los nombres.'], 502);
        }

        return response()->json(['success' => true, 'existe' => false] + $datos);
    }
}
