<?php

namespace App\Services;

use App\Models\Cita;

/**
 * Genera archivos .ics (iCalendar) para que el paciente guarde su cita en
 * Google Calendar, el calendario del celular u Outlook con un recordatorio.
 */
class CalendarioIcs
{
    public function paraCita(Cita $cita): string
    {
        $inicio = $cita->fecha_hora->copy()->utc();
        $fin = $inicio->copy()->addMinutes(AgendaCitas::DURACION_MINUTOS);
        $lugar = trim(($cita->posta?->nombre ?? 'Microred Socabaya').' - '.($cita->posta?->direccion ?? ''), ' -');
        $medico = $cita->medico?->name ?? 'Personal de la microred';

        $lineas = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Hampi//Microred Socabaya//ES',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:cita-'.$cita->id.'@'.(parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'hampi.local'),
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$inicio->format('Ymd\THis\Z'),
            'DTEND:'.$fin->format('Ymd\THis\Z'),
            'SUMMARY:'.$this->escapar('Cita de '.$cita->especialidad.' - Hampi'),
            'DESCRIPTION:'.$this->escapar("Atiende: {$medico}. Llega 15 minutos antes con tu DNI."),
            'LOCATION:'.$this->escapar($lugar),
            'BEGIN:VALARM',
            'TRIGGER:-PT2H',
            'ACTION:DISPLAY',
            'DESCRIPTION:'.$this->escapar('Tu cita en la posta es en 2 horas'),
            'END:VALARM',
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", array_map([$this, 'plegar'], $lineas))."\r\n";
    }

    private function escapar(string $texto): string
    {
        return str_replace(['\\', ';', ',', "\r\n", "\n"], ['\\\\', '\;', '\,', '\n', '\n'], $texto);
    }

    /** Las líneas iCalendar no deben superar 75 octetos. */
    private function plegar(string $linea): string
    {
        if (strlen($linea) <= 75) {
            return $linea;
        }

        $partes = [];

        while (strlen($linea) > 75) {
            $corte = 75;

            // No partir un carácter UTF-8 por la mitad.
            while ($corte > 0 && (ord($linea[$corte]) & 0xC0) === 0x80) {
                $corte--;
            }

            $partes[] = substr($linea, 0, $corte);
            $linea = ' '.substr($linea, $corte);
        }

        $partes[] = $linea;

        return implode("\r\n", $partes);
    }
}
