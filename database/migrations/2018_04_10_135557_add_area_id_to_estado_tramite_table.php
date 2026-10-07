<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAreaIdToEstadoTramiteTable extends Migration
{
    public function up()
    {
        Schema::table('estado_tramites', function (Blueprint $table) {
            $table->unsignedInteger('area_id')->nullable()->index();
            $table->unique(['nombre', 'area_id']);
        });
    }

    public function down()
    {
        Schema::table('estado_tramites', function (Blueprint $table) {
            $table->dropColumn('area_id');
        });
    }
}
