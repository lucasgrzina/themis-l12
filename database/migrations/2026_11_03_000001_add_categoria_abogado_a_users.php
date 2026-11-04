<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Categoria del abogado para el informe de liquidacion del Time.
 *
 * La categoria determina que tarifa (valor hora) del cliente se le aplica
 * a las horas cargadas por ese abogado. Es unica/global por abogado.
 * Valores de referencia: Abono, Semi Sr, Sr, Director.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('categoria_abogado', 50)->nullable()->after('time_level');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('categoria_abogado');
        });
    }
};
