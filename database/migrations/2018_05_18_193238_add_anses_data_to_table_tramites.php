<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAnsesDataToTableTramites extends Migration
{
    public function up()
    {
        Schema::table('tramite_clientes', function (Blueprint $table) {
            $table->unsignedInteger('estado_anses_id')->nullable()->index();
            $table->date('fecha_remision')->nullable();
            $table->date('fecha_remision_vto')->nullable();
        });
    }

    public function down()
    {
        Schema::table('tramite_clientes', function (Blueprint $table) {
            $table->dropColumn('estado_anses_id');
            $table->dropColumn('fecha_remision');
            $table->dropColumn('fecha_remision_vto');
        });
    }
}
