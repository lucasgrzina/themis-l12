<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTratamientoTipoTramitesTable extends Migration
{
    public function up()
    {
        Schema::table('tipo_tramites', function (Blueprint $table) {
            $table->string('tratamiento')->default(NULL)->nullable();
        });
    }

    public function down()
    {
        Schema::table('tipo_tramites', function (Blueprint $table) {
            $table->dropColumn('tratamiento');
        });
    }
}
