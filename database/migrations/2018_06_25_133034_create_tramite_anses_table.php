<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTramiteAnsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tramite_anses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tramite_id')->index();
            $table->unsignedInteger('estado_anses_id')->nullable()->index();
            $table->date('fecha_remision')->nullable();
            $table->date('fecha_remision_vto')->nullable();
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
        Schema::dropIfExists('tramite_anses');
    }
}
