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
        Schema::create('postas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: Centro de Salud San Martín de Socabaya
            $table->string('categoria')->default('I-3');
            $table->string('direccion'); // Ej: Av. Socabaya N° 300
            $table->string('telefono')->nullable();
            $table->string('horario_atencion')->default('07:30 - 19:30');
            $table->boolean('estado_activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postas');
    }
};
