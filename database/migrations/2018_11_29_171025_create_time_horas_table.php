<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTimeHorasTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('time_horas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->integer('cliente_id')->unsigned()->index();
            $table->integer('gestion_id')->unsigned()->index();
            $table->string('referencia', 500)->nullable();
            $table->boolean('facturar')->default(false);
            $table->text('descripcion')->nullable();
            $table->date('fecha')->index();
            $table->integer('minutos')->default(0);
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
        Schema::drop('time_horas');
    }
}
