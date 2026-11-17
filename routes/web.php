<?php

use App\Http\Controllers\Admin\CampanaController as AdminCampanaController;
use App\Http\Controllers\Admin\CiudadanoController as AdminCiudadanoController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EquipoController as AdminEquipoController;
use App\Http\Controllers\Admin\HorarioController as AdminHorarioController;
use App\Http\Controllers\Admin\TurnoController as AdminTurnoController;
use App\Http\Controllers\Admin\VacunaController as AdminVacunaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CiudadanoController;
use App\Http\Controllers\EmpleadoCitaController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\FotoPerfilController;
use App\Http\Controllers\RecuperarClaveController;
use App\Http\Controllers\HampiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal público
|--------------------------------------------------------------------------
*/

Route::get('/', [HampiController::class, 'index'])->name('inicio');
Route::get('/api/consultar-dni/{dni}', [HampiController::class, 'consultarDniReniec'])
    ->middleware('throttle:3,1');

// Acceso ciudadano: DNI + código de 6 dígitos.
Route::post('/ingreso-dni', [CiudadanoController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('ciudadano.login');
Route::get('/crear-codigo', [CiudadanoController::class, 'showRegistro'])->name('ciudadano.registro');
Route::post('/crear-codigo', [CiudadanoController::class, 'registrar'])
    ->middleware('throttle:5,10')
    ->name('ciudadano.registro.store');
Route::post('/cerrar-sesion-ciudadano', [CiudadanoController::class, 'logout'])->name('ciudadano.logout');

Route::middleware('ciudadano')->group(function () {
    Route::get('/mi-panel', [CiudadanoController::class, 'panel'])->name('ciudadano.panel');
    Route::get('/mi-panel/nueva-cita', [CiudadanoController::class, 'crearCita'])->name('ciudadano.citas.create');
    Route::get('/mi-panel/disponibilidad', [CiudadanoController::class, 'disponibilidad'])
        ->middleware('throttle:60,1')
        ->name('ciudadano.citas.disponibilidad');
    Route::post('/mi-panel/citas', [CiudadanoController::class, 'guardarCita'])
        ->middleware('throttle:10,1')
        ->name('ciudadano.citas.store');
    Route::post('/mi-panel/citas/{cita}/cancelar', [CiudadanoController::class, 'cancelarCita'])->name('ciudadano.citas.cancelar');
    Route::get('/mi-panel/citas/{cita}/calendario.ics', [CiudadanoController::class, 'calendario'])->name('ciudadano.citas.ics');
});

// Fotos de perfil guardadas en la base de datos.
Route::get('/fotos/{user}/{version}', [FotoPerfilController::class, 'show'])
    ->whereNumber('user')
    ->name('fotos.show');

Route::get('/medicos-hoy', [HampiController::class, 'verMedicos'])->name('medicos.index');
Route::get('/pizarrin-vacunas', [HampiController::class, 'verPizarrin'])->name('pizarrin.index');
Route::get('/campanas-preventivas', [HampiController::class, 'verCampanasPreventivas'])->name('campanas.index');
Route::get('/directorio-postas', [HampiController::class, 'verPostas'])->name('postas.index');

/*
|--------------------------------------------------------------------------
| Acceso administrativo (correo institucional)
|--------------------------------------------------------------------------
*/

Route::get('/personal/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/personal/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/personal/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Acceso del personal de salud (DNI + contraseña)
|--------------------------------------------------------------------------
*/

Route::get('/equipo/login', [EmpleadoController::class, 'showLogin'])->name('empleado.login');
Route::post('/equipo/login', [EmpleadoController::class, 'login'])->name('empleado.login.post');
Route::post('/equipo/logout', [EmpleadoController::class, 'logout'])->name('empleado.logout');

// Recuperación de contraseña del personal (enlace por correo).
Route::middleware('guest')->group(function () {
    Route::get('/equipo/olvide-mi-clave', [RecuperarClaveController::class, 'create'])->name('password.request');
    Route::post('/equipo/olvide-mi-clave', [RecuperarClaveController::class, 'store'])
        ->middleware('throttle:3,10')
        ->name('password.email');
    Route::get('/equipo/restablecer/{token}', [RecuperarClaveController::class, 'edit'])->name('password.reset');
    Route::post('/equipo/restablecer', [RecuperarClaveController::class, 'update'])
        ->middleware('throttle:5,10')
        ->name('password.update');
});

Route::middleware(['auth', 'role:doctor,enfermero,enfermera'])
    ->prefix('empleado')
    ->name('empleado.')
    ->group(function () {
        Route::get('/panel', [EmpleadoController::class, 'panel'])->name('panel');
        Route::post('/perfil', [EmpleadoController::class, 'updatePerfil'])->name('perfil.update');
    });

/*
|--------------------------------------------------------------------------
| Agenda de citas del médico
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:doctor'])
    ->prefix('empleado/citas')
    ->name('empleado.citas.')
    ->group(function () {
        Route::get('/', [EmpleadoCitaController::class, 'index'])->name('index');
        Route::get('/crear', [EmpleadoCitaController::class, 'create'])->name('create');
        Route::post('/', [EmpleadoCitaController::class, 'store'])->name('store');
        Route::get('/disponibilidad', [EmpleadoCitaController::class, 'disponibilidad'])->name('disponibilidad');
        Route::post('/{cita}/estado', [EmpleadoCitaController::class, 'actualizarEstado'])->name('estado');
        Route::post('/{cita}/codigo', [EmpleadoCitaController::class, 'nuevoCodigo'])->name('codigo');
    });

/*
|--------------------------------------------------------------------------
| Panel administrativo
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,admision'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Código de acceso del portal ciudadano (entrega en ventanilla).
        Route::post('/ciudadanos/codigo', [AdminCiudadanoController::class, 'codigo'])->name('ciudadanos.codigo');

        // Rol de médicos del día.
        Route::post('/turnos/guardar', [AdminTurnoController::class, 'store'])->name('turno.store');
        Route::post('/turnos/{id}/estado', [AdminTurnoController::class, 'updateEstado'])->name('turno.update');
        Route::delete('/turnos/{id}', [AdminTurnoController::class, 'destroy'])->name('turno.destroy');

        // Pizarrín de vacunas.
        Route::post('/pizarrin/guardar', [AdminVacunaController::class, 'store'])->name('vacuna.store');
        Route::delete('/pizarrin/{id}', [AdminVacunaController::class, 'destroy'])->name('vacuna.destroy');

        // Equipo de salud (médicos y enfermeros).
        Route::get('/equipo', [AdminEquipoController::class, 'index'])->name('equipo.index');
        Route::post('/equipo', [AdminEquipoController::class, 'store'])->name('equipo.store');
        Route::post('/equipo/{empleado}', [AdminEquipoController::class, 'update'])->name('equipo.update');
        Route::delete('/equipo/{empleado}', [AdminEquipoController::class, 'destroy'])->name('equipo.destroy');

        // Horarios mensuales.
        Route::get('/horarios', [AdminHorarioController::class, 'index'])->name('horarios.index');
        Route::post('/horarios/generar', [AdminHorarioController::class, 'generar'])->name('horarios.generar');
        Route::delete('/horarios/{empleado}/{anio}/{mes}', [AdminHorarioController::class, 'destroy'])->name('horarios.destroy');

        // Campañas preventivas.
        Route::get('/campanas', [AdminCampanaController::class, 'index'])->name('campanas.index');
        Route::post('/campanas', [AdminCampanaController::class, 'store'])->name('campanas.store');
        Route::post('/campanas/{campana}/estado', [AdminCampanaController::class, 'toggleEstado'])->name('campanas.estado');
        Route::delete('/campanas/{campana}', [AdminCampanaController::class, 'destroy'])->name('campanas.destroy');
    });
