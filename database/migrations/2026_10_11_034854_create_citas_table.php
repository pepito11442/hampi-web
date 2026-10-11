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
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciudadano_id')->constrained('ciudadanos')->onDelete('cascade');
            $table->foreignId('posta_id')->constrained('postas')->onDelete('cascade');
            $table->string('especialidad');
            $table->dateTime('fecha_hora');
            $table->enum('estado', ['Confirmada', 'Pendiente', 'Atendida', 'Cancelada'])->default('Confirmada');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
