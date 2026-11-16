<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Guarda la foto de perfil (ya reducida a WebP) dentro de la base.
     *
     * Los hostings gratuitos borran el disco en cada reinicio; así la foto
     * sobrevive sin depender de un almacenamiento externo.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->longText('foto_data')->nullable();
            $table->string('foto_mime', 40)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['foto_data', 'foto_mime']);
        });
    }
};
