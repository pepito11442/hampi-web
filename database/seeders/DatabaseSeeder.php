<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Posta;
use App\Models\VacunaAviso;
use App\Models\TurnoMedico;
use App\Models\Ciudadano;
use App\Models\Cita;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario enfermería
        User::create([
            'name' => 'Lic. Carmen Alfaro',
            'email' => 'enfermeria@hampi.pe',
            'password' => Hash::make('socabaya2024'),
            'role' => 'enfermera',
            'establecimiento' => 'C.S. San Martín de Socabaya'
        ]);

        // Posta principal
        $posta = Posta::create([
            'nombre' => 'Centro de Salud San Martín de Socabaya',
            'categoria' => 'I-3',
            'direccion' => 'Av. Socabaya N° 300, Socabaya',
            'telefono' => '(054) 436120',
            'horario_atencion' => '07:30 - 19:30'
        ]);

        // Pizarrín de Vacunas (Validado con la entrevista)
        VacunaAviso::create([
            'posta_id' => $posta->id,
            'titulo' => 'Vacuna Influenza Adulto Mayor',
            'grupo_objetivo' => 'Personas mayores de 60 años y personas con comorbilidades',
            'stock_estado' => 'Disponible',
            'requisitos' => 'Solo DNI físico',
            'es_urgente' => false
        ]);

        VacunaAviso::create([
            'posta_id' => $posta->id,
            'titulo' => 'Vacuna Neumococo (Pediátrico)',
            'grupo_objetivo' => 'Niños menores de 5 años',
            'stock_estado' => 'Disponible',
            'requisitos' => 'Carné de vacunación y DNI del menor',
            'es_urgente' => false
        ]);

        VacunaAviso::create([
            'posta_id' => $posta->id,
            'titulo' => 'Jornada de Vacunación Antirrábica Canina',
            'grupo_objetivo' => 'Mascotas de la comunidad de Socabaya',
            'stock_estado' => 'Disponible',
            'requisitos' => 'Acudir a la plaza principal este domingo',
            'es_urgente' => true
        ]);

        // Turnos Médicos de hoy
        TurnoMedico::create([
            'posta_id' => $posta->id,
            'medico_nombre' => 'Dr. E. Flores',
            'especialidad' => 'Medicina General',
            'turno' => 'Mañana',
            'consultorio' => 'Consultorio 2',
            'estado' => 'Atendiendo'
        ]);

        TurnoMedico::create([
            'posta_id' => $posta->id,
            'medico_nombre' => 'Dra. M. Paredes',
            'especialidad' => 'Pediatría',
            'turno' => 'Mañana',
            'consultorio' => 'Consultorio 4',
            'estado' => 'Turno Completo'
        ]);

        TurnoMedico::create([
            'posta_id' => $posta->id,
            'medico_nombre' => 'Lic. C. Alfaro',
            'especialidad' => 'Control CRED y Vacunación',
            'turno' => 'Tarde',
            'consultorio' => 'Tópico de Inmunizaciones',
            'estado' => 'Atendiendo'
        ]);

        // Ciudadano de prueba
        $ciudadano = Ciudadano::create([
            'dni' => '72345678',
            'nombres' => 'Rosa Elena',
            'apellidos' => 'Quispe Mamani'
        ]);

        Cita::create([
            'ciudadano_id' => $ciudadano->id,
            'posta_id' => $posta->id,
            'especialidad' => 'Medicina General',
            'fecha_hora' => now()->addDays(2)->setHour(8)->setMinute(30),
            'estado' => 'Confirmada'
        ]);
    }
}