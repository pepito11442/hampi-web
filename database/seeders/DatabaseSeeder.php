<?php

namespace Database\Seeders;

use App\Models\CampanaPreventiva;
use App\Models\Cita;
use App\Models\Ciudadano;
use App\Models\Posta;
use App\Models\TurnoMedico;
use App\Models\User;
use App\Models\VacunaAviso;
use App\Services\HorarioGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $passwordDemo = Hash::make('socabaya2024');

        // Cuenta administrativa (acceso por correo).
        User::updateOrCreate(
            ['email' => 'enfermeria@hampi.pe'],
            [
                'name' => 'Lic. Carmen Alfaro',
                'dni' => '40123456',
                'password' => $passwordDemo,
                'role' => User::ROL_ADMIN,
                'establecimiento' => 'C.S. San Martín de Socabaya',
                'telefono' => '954 123 456',
                'especialidad' => 'Enfermería / Gestión',
                'biografia' => 'Responsable de admisión y gestión de la Microred Socabaya.',
                'activo' => true,
            ]
        );

        // Personal de salud con acceso por DNI.
        $personal = [
            [
                'dni' => '45678912',
                'name' => 'Dr. Eduardo Flores Quispe',
                'email' => 'eduardo.flores@hampi.pe',
                'role' => User::ROL_DOCTOR,
                'especialidad' => 'Medicina General',
                'telefono' => '987 111 222',
                'biografia' => 'Médico general con enfoque en atención primaria y prevención comunitaria.',
                'titulos' => "Médico Cirujano · Universidad Nacional de San Agustín\nDiplomado en Salud Pública",
                'experiencia' => "Atención en consultorio externo y campañas de salud en la Microred Socabaya.",
            ],
            [
                'dni' => '41234567',
                'name' => 'Dra. María Paredes Salas',
                'email' => 'maria.paredes@hampi.pe',
                'role' => User::ROL_DOCTOR,
                'especialidad' => 'Pediatría',
                'telefono' => '987 333 444',
                'biografia' => 'Pediatra dedicada al control del niño sano y la prevención de enfermedades infantiles.',
                'titulos' => "Médico Cirujano · Universidad Católica de Santa María\nEspecialidad en Pediatría",
                'experiencia' => "Control CRED, vacunación infantil y atención de urgencias pediátricas.",
            ],
            [
                'dni' => '47891234',
                'name' => 'Lic. Rosa Mendoza Vargas',
                'email' => 'rosa.mendoza@hampi.pe',
                'role' => User::ROL_ENFERMERO,
                'especialidad' => 'Enfermería',
                'telefono' => '987 555 666',
                'biografia' => 'Enfermera de atención primaria, orientada al cuidado familiar y la educación para la salud.',
                'titulos' => "Licenciada en Enfermería · Universidad Nacional de San Agustín",
                'experiencia' => "Control de signos vitales, curaciones y consejería en salud comunitaria.",
            ],
            [
                'dni' => '46789123',
                'name' => 'Téc. Luis Huamán Ríos',
                'email' => 'luis.huaman@hampi.pe',
                'role' => User::ROL_ENFERMERO,
                'especialidad' => 'Enfermería / Inmunizaciones',
                'telefono' => '987 777 888',
                'biografia' => 'Técnico en enfermería encargado de inmunizaciones y del pizarrín de vacunas.',
                'titulos' => "Técnico en Enfermería · Instituto Superior Tecnológico",
                'experiencia' => "Vacunación regular, campañas de inmunización y registro de stock.",
            ],
        ];

        $usuariosPersonal = [];

        foreach ($personal as $datos) {
            $usuariosPersonal[] = User::updateOrCreate(
                ['dni' => $datos['dni']],
                array_merge($datos, [
                    'password' => $passwordDemo,
                    'establecimiento' => 'C.S. San Martín de Socabaya',
                    'activo' => true,
                ])
            );
        }

        // Posta principal.
        $posta = Posta::updateOrCreate(
            ['nombre' => 'Centro de Salud San Martín de Socabaya'],
            [
                'categoria' => 'I-3',
                'direccion' => 'Av. Socabaya N° 300, Socabaya',
                'telefono' => '(054) 436120',
                'horario_atencion' => 'Lun a vie 07:00 - 19:30 · Sáb 07:00 - 15:00',
                'estado_activo' => true,
            ]
        );

        // Pizarrín de vacunas.
        VacunaAviso::updateOrCreate(
            ['posta_id' => $posta->id, 'titulo' => 'Vacuna contra la influenza'],
            [
                'grupo_objetivo' => 'Adultos mayores de 60 años y personas con comorbilidades',
                'stock_estado' => 'Disponible',
                'requisitos' => 'DNI físico y carné de vacunación',
                'observacion' => 'Atención en el consultorio de inmunizaciones de lunes a sábado.',
                'es_urgente' => true,
            ]
        );

        VacunaAviso::updateOrCreate(
            ['posta_id' => $posta->id, 'titulo' => 'Vacuna VPH para escolares'],
            [
                'grupo_objetivo' => 'Niñas y niños de 9 a 13 años',
                'stock_estado' => 'Disponible',
                'requisitos' => 'DNI del menor y carné de vacunación',
                'observacion' => 'Se requiere autorización del padre, madre o apoderado.',
                'es_urgente' => false,
            ]
        );

        VacunaAviso::updateOrCreate(
            ['posta_id' => $posta->id, 'titulo' => 'Vacuna neumocócica'],
            [
                'grupo_objetivo' => 'Adultos mayores de 65 años',
                'stock_estado' => 'Por agotarse',
                'requisitos' => 'DNI físico',
                'observacion' => 'Stock limitado; confirmar disponibilidad en admisión.',
                'es_urgente' => false,
            ]
        );

        // Rol público de médicos del día.
        TurnoMedico::updateOrCreate(
            ['posta_id' => $posta->id, 'medico_nombre' => 'Dr. Eduardo Flores Quispe'],
            [
                'especialidad' => 'Medicina General',
                'turno' => 'Mañana',
                'consultorio' => 'Consultorio 1',
                'estado' => 'Atendiendo',
            ]
        );

        TurnoMedico::updateOrCreate(
            ['posta_id' => $posta->id, 'medico_nombre' => 'Dra. María Paredes Salas'],
            [
                'especialidad' => 'Pediatría',
                'turno' => 'Tarde',
                'consultorio' => 'Consultorio 2',
                'estado' => 'Turno Completo',
            ]
        );

        // Ciudadano y cita de demostración.
        $ciudadano = Ciudadano::updateOrCreate(
            ['dni' => '72345678'],
            [
                'nombres' => 'Rosa Elena',
                'apellidos' => 'Quispe Mamani',
                'telefono' => '987 654 321',
            ]
        );

        Cita::firstOrCreate(
            [
                'ciudadano_id' => $ciudadano->id,
                'posta_id' => $posta->id,
                'especialidad' => 'Medicina General',
                'fecha_hora' => now()->startOfMonth()->addDays(9)->setTime(9, 0),
            ],
            ['estado' => 'Confirmada']
        );

        // Campañas preventivas de ejemplo.
        $campanas = [
            [
                'titulo' => 'Control de presión arterial y diabetes',
                'grupo_objetivo' => 'Adultos mayores de 40 años',
                'descripcion' => 'Medición de presión arterial, control de glucosa y consejería para prevenir complicaciones cardiovasculares.',
                'lugar' => 'C.S. San Martín de Socabaya',
                'fecha_inicio' => now()->startOfMonth()->addDays(4)->toDateString(),
                'fecha_fin' => now()->startOfMonth()->addDays(4)->toDateString(),
            ],
            [
                'titulo' => 'Descarte de anemia infantil',
                'grupo_objetivo' => 'Niñas y niños menores de 5 años',
                'descripcion' => 'Evaluación nutricional, dosaje de hemoglobina y orientación alimentaria para las familias.',
                'lugar' => 'C.S. San Martín de Socabaya',
                'fecha_inicio' => now()->startOfMonth()->addDays(11)->toDateString(),
                'fecha_fin' => now()->startOfMonth()->addDays(12)->toDateString(),
            ],
            [
                'titulo' => 'Salud bucal para escolares',
                'grupo_objetivo' => 'Niñas y niños de 6 a 12 años',
                'descripcion' => 'Evaluación odontológica, aplicación de flúor y enseñanza de técnicas de cepillado.',
                'lugar' => 'I.E. San Martín de Socabaya',
                'fecha_inicio' => now()->startOfMonth()->addDays(18)->toDateString(),
                'fecha_fin' => now()->startOfMonth()->addDays(18)->toDateString(),
            ],
            [
                'titulo' => 'Orientación en planificación familiar',
                'grupo_objetivo' => 'Personas en edad fértil',
                'descripcion' => 'Consejería gratuita sobre métodos anticonceptivos y cuidado de la salud sexual y reproductiva.',
                'lugar' => 'C.S. San Martín de Socabaya',
                'fecha_inicio' => now()->startOfMonth()->addDays(25)->toDateString(),
                'fecha_fin' => now()->startOfMonth()->addDays(25)->toDateString(),
            ],
        ];

        foreach ($campanas as $campana) {
            CampanaPreventiva::updateOrCreate(
                ['titulo' => $campana['titulo']],
                array_merge($campana, ['activa' => true])
            );
        }

        // Horarios de ejemplo para el mes actual.
        $generador = app(HorarioGenerator::class);

        foreach ($usuariosPersonal as $usuario) {
            $generador->generar($usuario, now()->year, now()->month);
        }
    }
}
