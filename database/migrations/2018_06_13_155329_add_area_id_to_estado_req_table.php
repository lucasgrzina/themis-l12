<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAreaIdToEstadoReqTable extends Migration
{
    public function up()
    {
        Schema::table('estado_requerimientos', function (Blueprint $table) {
            $table->unique(['nombre', 'area_id']);
        });
    }

    public function down()
    {
    }
}
