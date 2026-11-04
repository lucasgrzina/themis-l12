<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tipo de cambio mensual para el informe de liquidacion del Time.
 *
 * Carga manual, un valor por periodo (mes). periodo se guarda como el primer
 * dia del mes (YYYY-MM-01). 'nombre' permite distinguir cotizaciones (ej. MEP).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_tipos_cambio', function (Blueprint $table) {
            $table->increments('id');
            $table->date('periodo')->index();
            $table->string('nombre', 30)->default('MEP');
            $table->decimal('valor', 14, 4)->default(0);
            $table->timestamps();

            $table->unique(['periodo', 'nombre'], 'time_tipos_cambio_periodo_nombre_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_tipos_cambio');
    }
};
