<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTimeReferenciasTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('time_referencias', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cliente_id')->unsigned()->index();
            $table->string('nombre')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['cliente_id','nombre']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('time_referencias');
    }
}
