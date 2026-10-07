<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEmpresasReferenciaTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('empresas_referencia', function (Blueprint $table) {
            $table->increments('id');
            $table->string('razon_social')->index();
            $table->string('direccion')->nullable();
            $table->string('telefonos')->nullable();
            $table->string('localidad')->nullable();
            $table->string('persona_referencia')->nullable();
            $table->string('cuit', 20)->nullable()->index();
            $table->integer('id_cond_iva')->nullable();
            $table->text('observaciones')->nullable();
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
        Schema::drop('empresas_referencia');
    }
}
