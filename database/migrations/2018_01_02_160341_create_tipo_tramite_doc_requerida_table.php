<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTipoTramiteDocRequeridaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tipo_tramite_doc_requerida', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tipo_tramite_id')->index();
            $table->unsignedInteger('doc_requerida_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tipo_tramite_doc_requerida');
    }
}
