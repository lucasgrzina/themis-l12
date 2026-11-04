<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tasas de impuestos / retenciones globales para el informe de liquidacion.
 *
 * Globales con historial por vigencia: se toma la vigencia mas reciente
 * <= periodo del informe. tasa como fraccion (0.025 = 2,5%).
 * El IVA se almacena aqui como tasa, pero solo se aplica a los clientes que
 * tienen aplica_iva = true (modelo presupuesto). codigos: imp_cto_dto,
 * sircreb, ret_gcias, iva.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_retenciones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo', 30)->index();
            $table->string('nombre', 100);
            $table->decimal('tasa', 7, 4)->default(0);
            $table->date('vigencia_desde')->nullable();
            $table->timestamps();
        });

        // Tasas base con vigencia NULL = aplican siempre (fallback historico).
        $hoy = now()->toDateString();
        \DB::table('time_retenciones')->insert([
            ['codigo' => 'imp_cto_dto', 'nombre' => 'Imp. al cto y dto', 'tasa' => 0.012, 'vigencia_desde' => null, 'created_at' => $hoy, 'updated_at' => $hoy],
            ['codigo' => 'sircreb',     'nombre' => 'SIRCREB',           'tasa' => 0.025, 'vigencia_desde' => null, 'created_at' => $hoy, 'updated_at' => $hoy],
            ['codigo' => 'ret_gcias',   'nombre' => 'Retencion de Ganancias', 'tasa' => 0.175, 'vigencia_desde' => null, 'created_at' => $hoy, 'updated_at' => $hoy],
            ['codigo' => 'iva',         'nombre' => 'IVA',               'tasa' => 0.21,  'vigencia_desde' => null, 'created_at' => $hoy, 'updated_at' => $hoy],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('time_retenciones');
    }
};
