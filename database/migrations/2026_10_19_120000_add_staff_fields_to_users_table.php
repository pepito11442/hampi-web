<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Amplía la tabla de usuarios para las cuentas del personal de salud:
     * acceso por DNI, perfil profesional y foto.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('dni', 8)->nullable()->unique();
            $table->string('telefono', 20)->nullable();
            $table->string('especialidad', 120)->nullable();
            $table->text('biografia')->nullable();
            $table->text('titulos')->nullable();
            $table->text('experiencia')->nullable();
            $table->string('foto_perfil')->nullable();
            $table->boolean('activo')->default(true);
        });

        // El rol deja de ser un enum cerrado para permitir doctor / enfermero.
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('enfermera')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['dni']);
            $table->dropColumn([
                'dni',
                'telefono',
                'especialidad',
                'biografia',
                'titulos',
                'experiencia',
                'foto_perfil',
                'activo',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'enfermera', 'admision'])->default('enfermera')->change();
        });
    }
};
