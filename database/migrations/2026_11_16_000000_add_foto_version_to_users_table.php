<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Huella corta de la foto de perfil.
     *
     * Permite armar la URL de la foto (y saber si existe) sin leer
     * users.foto_data, que pesa decenas de KB por persona.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('foto_version', 12)->nullable()->after('foto_mime');
        });

        DB::table('users')
            ->whereNotNull('foto_data')
            ->select(['id', 'foto_data'])
            ->orderBy('id')
            ->each(function ($usuario) {
                DB::table('users')->where('id', $usuario->id)->update([
                    'foto_version' => substr(md5((string) $usuario->foto_data), 0, 12),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('foto_version');
        });
    }
};
