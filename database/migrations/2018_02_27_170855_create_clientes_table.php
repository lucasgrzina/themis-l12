<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateClientesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->increments('id');
            $table->char('personeria', 1)->nullable()->index();
            $table->date('fecha_entrevista')->nullable();
            $table->string('nombre_completo', 150)->nullable();
            $table->string('cuit', 20)->nullable()->index();
            $table->char('sexo', 1)->nullable();
            $table->integer('tipo_doc_id')->nullable()->index();
            $table->string('nro_doc', 20)->nullable()->index();
            $table->char('nacionalidad', 1)->nullable();
            $table->date('fecha_ing_pais')->nullable();
            $table->date('fecha_nac')->nullable();
            $table->integer('pais_id')->nullable()->index();
            $table->string('cp', 10)->nullable();
            $table->string('localidad', 80)->nullable();
            $table->integer('provincia_id')->nullable()->index();
            $table->string('clp_extranjero', 1000)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->json('emails')->nullable();
            $table->json('telefonos')->nullable();
            $table->char('categoria', 1)->nullable()->index();
            $table->integer('empresa_referencia_id')->nullable()->index();
            $table->integer('tipo_aporte_id')->nullable()->index();
            $table->char('estado_civil', 2)->nullable();
            $table->string('nombre_conyuge', 100)->nullable();
            $table->string('apellido_conyuge', 100)->nullable();
            $table->integer('tipo_doc_conyuge_id')->nullable()->index();
            $table->string('nro_doc_conyuge', 20)->nullable();
            $table->string('email_conyuge', 100)->nullable();
            $table->string('telefono_conyuge', 100)->nullable();
            $table->string('ubicacion_carpeta', 200)->nullable();
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
        Schema::drop('clientes');
    }
}
