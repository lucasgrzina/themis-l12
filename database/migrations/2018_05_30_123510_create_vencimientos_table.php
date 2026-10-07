<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVencimientosTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vencimientos', function (Blueprint $table) {
            $table->increments('id');
            $table->datetime('fecha')->index();
            $table->integer('cliente_id')->unsigned()->index();
            $table->integer('requerimiento_id')->unsigned()->index();
            $table->integer('tramite_id')->unsigned()->index();
            $table->morphs('vencible');
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
        Schema::drop('vencimientos');
    }
}
