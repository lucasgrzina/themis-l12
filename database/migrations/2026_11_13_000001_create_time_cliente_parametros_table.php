<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Parametros de liquidacion por cliente con vigencia ("desde"):
 * - horas_director: horas mensuales del director (ya no se cargan como un usuario).
 * - pct_director: % de participacion del director sobre sus horas netas de impuestos.
 * - horas_piso: horas base que se cobran siempre (el excedente se factura aparte).
 */
class CreateTimeClienteParametrosTable extends Migration
{
    public function up()
    {
        Schema::create('time_cliente_parametros', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cliente_id')->index();
            $table->date('vigencia_desde')->nullable();
            $table->decimal('horas_director', 8, 2)->default(0);
            $table->decimal('pct_director', 7, 4)->default(0);
            $table->decimal('horas_piso', 8, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['cliente_id', 'vigencia_desde'], 'time_cliente_parametros_lookup_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('time_cliente_parametros');
    }
}
