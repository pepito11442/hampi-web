<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registra quién reservó la cita (personal o el propio ciudadano) y
     * agrega índices a las columnas por las que más se filtra y ordena.
     */
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->string('origen', 20)->default('personal')->after('estado');
            $table->index('fecha_hora');
            $table->index(['user_id', 'fecha_hora']);
        });

        Schema::table('vacuna_avisos', function (Blueprint $table) {
            $table->index('stock_estado');
        });
    }

    public function down(): void
    {
        Schema::table('vacuna_avisos', function (Blueprint $table) {
            $table->dropIndex(['stock_estado']);
        });

        Schema::table('citas', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'fecha_hora']);
            $table->dropIndex(['fecha_hora']);
            $table->dropColumn('origen');
        });
    }
};
