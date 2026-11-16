<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Código de acceso del ciudadano.
     *
     * El DNI por sí solo no es un secreto: cualquiera lo conoce. Para ver
     * citas se exige además un código de 6 dígitos que se guarda con hash.
     */
    public function up(): void
    {
        Schema::table('ciudadanos', function (Blueprint $table) {
            $table->string('codigo_acceso')->nullable()->after('telefono');
            $table->timestamp('codigo_generado_at')->nullable()->after('codigo_acceso');
        });
    }

    public function down(): void
    {
        Schema::table('ciudadanos', function (Blueprint $table) {
            $table->dropColumn(['codigo_acceso', 'codigo_generado_at']);
        });
    }
};
