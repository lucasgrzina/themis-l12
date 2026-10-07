<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReparticionOrigenTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reparticion_origen', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre')->index();
            $table->string('direccion')->nullable();
            $table->string('localidad', 100)->nullable();
            $table->string('telefono', 150)->nullable();
            $table->boolean('vigente')->default(false);
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
        Schema::drop('reparticion_origen');
    }
}
