<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Campañas preventivas publicadas por la Microred Socabaya.
     */
    public function up(): void
    {
        Schema::create('campanas_preventivas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 150);
            $table->string('grupo_objetivo', 150)->nullable();
            $table->text('descripcion');
            $table->string('lugar', 150)->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campanas_preventivas');
    }
};
