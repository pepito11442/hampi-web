<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Horarios mensuales del personal de salud.
     * Cada fila representa un turno asignado a un día concreto.
     */
    public function up(): void
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('fecha');
            $table->string('turno', 20); // manana | tarde | completo | sabado
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->decimal('horas', 4, 1);
            $table->timestamps();

            $table->unique(['user_id', 'fecha']);
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
