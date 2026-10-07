<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTramiteClientesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tramite_clientes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('area_id')->unsigned()->index();
            $table->integer('cliente_id')->unsigned()->index();
            $table->integer('requerimiento_id')->unsigned()->index();
            $table->string('expediente', 50)->nullable()->index();
            $table->date('fecha_inicio')->nullable();
            $table->integer('rep_origen_id')->default(0)->unsigned()->index();
            $table->integer('estado_tramite_id')->unsigned()->index();
            $table->string('secuencia', 50)->nullable();
            $table->integer('oficina_ingreso_id')->nullable()->unsigned()->index();
            $table->integer('oficina_egreso_id')->nullable()->unsigned()->index();
            $table->boolean('resolucion')->nullable()->default(false);
            $table->integer('tipo_resolucion')->nullable()->default(2);
            $table->string('nro_beneficio', 50)->nullable();
            $table->date('fecha_beneficio')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('archivar')->nullable()->default(false);
            $table->datetime('fecha_archivo')->nullable();
            $table->integer('usuario_archivo_id')->nullable()->unsigned()->index();
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_egreso')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tramite_clientes');
    }
}
