<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAreaToTipoTramite extends Migration
{
    public function up()
    {
        Schema::table('tipo_tramites', function (Blueprint $table) {
            $table->unsignedInteger('area_id')->index();
        });
    }

    public function down()
    {
        Schema::table('tipo_tramites', function (Blueprint $table) {
            $table->dropColumn('area_id');
        });
    }
}
