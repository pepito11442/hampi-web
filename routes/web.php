<?php
// routes/web.php
Route::get('/test', function () {
    return '¡Laravel está vivo!';
});
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HampiController;
use App\Http\Controllers\AuthController;

// --- Rutas Públicas y Acceso Ciudadano por DNI ---
Route::get('/', [HampiController::class, 'index'])->name('inicio');
Route::get('/api/consultar-dni/{dni}', [HampiController::class, 'consultarDniReniec']);
Route::post('/ingreso-dni', [HampiController::class, 'accesoPorDni'])->name('ciudadano.login');
Route::get('/mi-panel', [HampiController::class, 'panelCiudadano'])->name('ciudadano.panel');
Route::get('/cerrar-sesion-ciudadano', [HampiController::class, 'logoutCiudadano'])->name('ciudadano.logout');

// --- Pistas y Módulos Informativos Ciudadanos ---
Route::get('/medicos-hoy', [HampiController::class, 'verMedicos'])->name('medicos.index');
Route::get('/pizarrin-vacunas', [HampiController::class, 'verPizarrin'])->name('pizarrin.index');
Route::get('/directorio-postas', [HampiController::class, 'verPostas'])->name('postas.index');

// --- Autenticación Administrativa (Personal de Salud) ---
Route::get('/personal/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/personal/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/personal/logout', [AuthController::class, 'logout'])->name('logout');

// --- Panel Administrativo Protegido (CRUD 50%) ---
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [HampiController::class, 'adminDashboard'])->name('admin.dashboard');
    
    // CRUD Pizarrín de Vacunas
    Route::post('/pizarrin/guardar', [HampiController::class, 'storeVacuna'])->name('admin.vacuna.store');
    Route::post('/pizarrin/{id}/estado', [HampiController::class, 'updateVacunaEstado'])->name('admin.vacuna.update');
    Route::delete('/pizarrin/{id}', [HampiController::class, 'destroyVacuna'])->name('admin.vacuna.destroy');

    // Gestión de Turnos de Médicos
    Route::post('/turnos/{id}/estado', [HampiController::class, 'updateTurnoEstado'])->name('admin.turno.update');
});