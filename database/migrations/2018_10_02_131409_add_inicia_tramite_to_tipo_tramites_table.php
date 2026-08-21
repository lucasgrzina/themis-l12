<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIniciaTramiteToTipoTramitesTable extends Migration
{
    public function up()
    {
        Schema::table('tipo_tramites', function (Blueprint $table) {
            $table->boolean('inicia_tramite')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tipo_tramites', function (Blueprint $table) {
            $table->dropColumn('inicia_tramite');
        });
    }
}
