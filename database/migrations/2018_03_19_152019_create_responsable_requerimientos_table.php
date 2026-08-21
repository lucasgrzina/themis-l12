<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateResponsableRequerimientosTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('responsable_requerimientos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('requerimiento_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->index();
            $table->date('fecha_asignacion')->nullable();
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
        Schema::drop('responsable_requerimientos');
    }
}
