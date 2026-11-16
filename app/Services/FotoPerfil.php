<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;

/**
 * Reduce la foto de perfil antes de guardarla.
 *
 * Una foto de celular pesa 2–5 MB; recortada a 400×400 en WebP queda en
 * unos 20–40 KB, carga al instante y cabe sin problema en la base de datos.
 */
class FotoPerfil
{
    public const LADO = 400;

    /**
     * @return array{data: string, mime: string} Imagen en base64 y su tipo.
     */
    public function procesar(UploadedFile $archivo): array
    {
        $contenido = (string) file_get_contents($archivo->getRealPath());

        if ($contenido === '') {
            throw new RuntimeException('No se pudo leer la imagen.');
        }

        $original = ['data' => base64_encode($contenido), 'mime' => $archivo->getMimeType() ?: 'image/jpeg'];

        // Sin GD, o con un formato que GD no entiende, se guarda tal cual:
        // la validación ya garantiza que es una imagen de máximo 2 MB.
        if (! function_exists('imagecreatefromstring')) {
            return $original;
        }

        $origen = @imagecreatefromstring($contenido);

        if (! $origen) {
            return $original;
        }

        $origen = $this->corregirOrientacion($origen, $archivo);

        // Recorte cuadrado centrado.
        $ancho = imagesx($origen);
        $alto = imagesy($origen);
        $lado = min($ancho, $alto);
        $destino = imagecreatetruecolor(self::LADO, self::LADO);
        imagealphablending($destino, false);
        imagesavealpha($destino, true);
        imagecopyresampled(
            $destino, $origen,
            0, 0,
            (int) (($ancho - $lado) / 2), (int) (($alto - $lado) / 2),
            self::LADO, self::LADO,
            $lado, $lado
        );

        ob_start();

        if (function_exists('imagewebp')) {
            imagewebp($destino, null, 80);
            $mime = 'image/webp';
        } else {
            imagejpeg($destino, null, 82);
            $mime = 'image/jpeg';
        }

        $binario = (string) ob_get_clean();

        imagedestroy($origen);
        imagedestroy($destino);

        return ['data' => base64_encode($binario), 'mime' => $mime];
    }

    /** Las fotos de celular vienen giradas según el EXIF. */
    private function corregirOrientacion(\GdImage $imagen, UploadedFile $archivo): \GdImage
    {
        if (! function_exists('exif_read_data') || ! in_array($archivo->getMimeType(), ['image/jpeg', 'image/jpg'], true)) {
            return $imagen;
        }

        $exif = @exif_read_data($archivo->getRealPath());
        $angulo = match ((int) ($exif['Orientation'] ?? 1)) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        return $angulo ? (imagerotate($imagen, $angulo, 0) ?: $imagen) : $imagen;
    }
}
