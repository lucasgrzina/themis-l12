<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddArea5DataToTableTramites extends Migration
{
    public function up()
    {
        Schema::table('tramite_clientes', function (Blueprint $table) {
            $table->date('fecha_vto_directorio')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('tramite_clientes', function (Blueprint $table) {
            $table->dropColumn('fecha_vto_directorio');
        });
    }
}
