<?php

use App\Http\Controllers\Admin\CampanaController as AdminCampanaController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EquipoController as AdminEquipoController;
use App\Http\Controllers\Admin\HorarioController as AdminHorarioController;
use App\Http\Controllers\Admin\TurnoController as AdminTurnoController;
use App\Http\Controllers\Admin\VacunaController as AdminVacunaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmpleadoCitaController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\HampiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal público
|--------------------------------------------------------------------------
*/

Route::get('/', [HampiController::class, 'index'])->name('inicio');
Route::get('/api/consultar-dni/{dni}', [HampiController::class, 'consultarDniReniec'])
    ->middleware('throttle:10,1');
Route::post('/ingreso-dni', [HampiController::class, 'accesoPorDni'])->name('ciudadano.login');
Route::get('/mi-panel', [HampiController::class, 'panelCiudadano'])->name('ciudadano.panel');
Route::get('/cerrar-sesion-ciudadano', [HampiController::class, 'logoutCiudadano'])->name('ciudadano.logout');

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
