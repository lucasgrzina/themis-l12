<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTramiteConciliacion extends Migration
{
    public function up()
    {
        Schema::create('tramite_conciliacion', function (Blueprint $table) {
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
        Schema::drop('tramite_conciliacion');
    }

}
