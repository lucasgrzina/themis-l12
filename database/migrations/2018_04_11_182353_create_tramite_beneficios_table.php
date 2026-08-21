<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTramiteBeneficiosTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tramite_beneficios', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tramite_id')->index();
            $table->json('detalle');
            $table->date('fecha_cobro');
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
        Schema::drop('tramite_beneficios');
    }
}
