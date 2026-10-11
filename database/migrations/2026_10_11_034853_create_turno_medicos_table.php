<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('turno_medicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posta_id')->constrained('postas')->onDelete('cascade');
            $table->string('medico_nombre');
            $table->string('especialidad'); // Medicina General, Pediatría, etc.
            $table->enum('turno', ['Mañana', 'Tarde'])->default('Mañana');
            $table->string('consultorio')->default('Consultorio 1');
            $table->enum('estado', ['Atendiendo', 'Turno Completo', 'Reprogramado'])->default('Atendiendo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turno_medicos');
    }
};
