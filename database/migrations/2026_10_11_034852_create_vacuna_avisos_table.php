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
        Schema::create('vacuna_avisos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posta_id')->constrained('postas')->onDelete('cascade');
            $table->string('titulo'); // Ej: Vacuna Influenza
            $table->string('grupo_objetivo'); // Ej: Adultos mayores de 60 años
            $table->enum('stock_estado', ['Disponible', 'Por agotarse', 'Agotado'])->default('Disponible');
            $table->string('requisitos')->default('Presentar DNI físico');
            $table->text('observacion')->nullable();
            $table->boolean('es_urgente')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacuna_avisos');
    }
};
