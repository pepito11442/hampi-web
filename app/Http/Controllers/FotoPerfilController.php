<?php

namespace App\Http\Controllers;

use App\Models\User;

/**
 * Sirve las fotos de perfil guardadas en la base de datos.
 *
 * La URL incluye una huella del contenido, así que el navegador puede
 * guardarla en caché un año: si la foto cambia, cambia la URL.
 */
class FotoPerfilController extends Controller
{
    public function show(int $user, string $version)
    {
        $usuario = User::query()
            ->whereKey($user)
            ->whereNotNull('foto_data')
            ->first(['id', 'foto_data', 'foto_mime']);

        abort_unless($usuario && hash_equals($usuario->fotoVersion(), $version), 404);

        return response(base64_decode($usuario->foto_data), 200, [
            'Content-Type' => $usuario->foto_mime ?: 'image/webp',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
