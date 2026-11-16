<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Carga los datos de demostración solo si la base está vacía.
 * Lo usa el arranque del contenedor (SEED_DEMO=true) para que un
 * reinicio no duplique ni pise datos reales.
 */
Artisan::command('hampi:sembrar-si-vacia', function () {
    if (\App\Models\User::query()->exists()) {
        $this->info('La base ya tiene usuarios: no se cargan datos de demostración.');

        return 0;
    }

    $this->call('db:seed', ['--force' => true]);
    $this->info('Datos de demostración cargados.');

    return 0;
})->purpose('Carga los datos demo solo si no hay usuarios');
