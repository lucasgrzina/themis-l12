<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAreaIdEstadoRequerimientosTable extends Migration
{
    public function up()
    {
        Schema::table('estado_requerimientos', function (Blueprint $table) {
            $table->unsignedInteger('area_id')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('estado_requerimientos', function (Blueprint $table) {
            $table->dropColumn('area_id');
        });
    }
}
