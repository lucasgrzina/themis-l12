<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTramiteExpedientesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tramite_expedientes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tramite_id')->index();
            $table->unsignedInteger('area_id')->index();
            $table->text('nro_expediente', 50);
            $table->unsignedInteger('juzgado_id')->index();
            $table->date('fecha');
            $table->unsignedInteger('estado_id')->index();
            $table->boolean('vuelta_anses');
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
        Schema::drop('tramite_expedientes');
    }
}
