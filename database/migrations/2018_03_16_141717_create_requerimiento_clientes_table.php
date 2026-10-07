<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRequerimientoClientesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requerimiento_clientes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('area_id')->unsigned();
            $table->integer('cliente_id')->unsigned();
            $table->integer('colega_id')->unsigned()->nullable();
            $table->integer('tipo_tramite_id')->unsigned();
            $table->string('recomendado', 100)->nullable();
            $table->string('nombre_causante', 100)->nullable();
            $table->integer('tipo_doc_id_causante')->unsigned()->nullable();
            $table->string('nro_doc_causante', 30)->nullable();
            $table->string('domicilio_causante', 500)->nullable();
            $table->string('estado_civil', 2)->nullable();
            $table->date('fecha_mat_causante')->nullable();
            $table->date('fecha_conv_causante')->nullable();
            $table->date('fecha_fallecimiento_causante')->nullable();
            $table->boolean('hijos')->nullable();
            $table->integer('req_nec_id')->unsigned()->nullable();
            $table->integer('estado_req_id')->unsigned();
            $table->integer('tramite_id')->unsigned()->nullable();
            $table->date('fecha_turno')->nullable();
            $table->string('hora_turno', 10)->nullable();
            $table->integer('rep_origen_id')->unsigned()->nullable();
            $table->json('documentacion')->nullable();
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
        Schema::drop('requerimiento_clientes');
    }
}
