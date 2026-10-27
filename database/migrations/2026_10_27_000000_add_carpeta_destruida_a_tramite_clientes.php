<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marca de destruccion de carpeta fisica por tramite.
 *
 * El archivado automatico solo setea `archivar`. La destruccion de la carpeta
 * fisica es una accion MANUAL posterior que se registra con esta marca
 * (quien y cuando), gestionada desde el informe de tramites historicos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tramite_clientes', function (Blueprint $table) {
            $table->boolean('carpeta_destruida')->default(false)->after('usuario_archivo_id')->index();
            $table->date('fecha_destruccion')->nullable()->after('carpeta_destruida');
            $table->unsignedInteger('usuario_destruccion_id')->nullable()->after('fecha_destruccion');
        });
    }

    public function down(): void
    {
        Schema::table('tramite_clientes', function (Blueprint $table) {
            $table->dropColumn(['carpeta_destruida', 'fecha_destruccion', 'usuario_destruccion_id']);
        });
    }
};
