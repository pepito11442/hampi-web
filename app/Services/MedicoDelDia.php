<?php

namespace App\Services;

/**
 * Médico que forma parte de la agenda de un día.
 *
 * Puede venir del horario mensual generado (fuente "horario") o del
 * rol manual histórico de la tabla turno_medicos (fuente "manual").
 */
class MedicoDelDia
{
    public function __construct(
        public string $clave,
        public string $medico_nombre,
        public string $especialidad,
        public string $turno,
        public string $turno_clave,
        public ?string $hora_inicio,
        public ?string $hora_fin,
        public string $consultorio,
        public string $estado,
        public string $fuente,
        public bool $en_manana,
        public bool $en_tarde,
        public ?int $user_id = null,
        public ?int $manual_id = null,
        public ?int $horario_id = null,
    ) {
    }
}
