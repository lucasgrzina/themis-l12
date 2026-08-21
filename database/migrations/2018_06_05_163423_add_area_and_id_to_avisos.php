<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAreaAndIdToAvisos extends Migration
{
    public function up()
    {
        Schema::table('aviso_clientes', function (Blueprint $table) {
            $table->integer('type_id')->index();
            $table->char('type',1)->default('U')->index();
        });
    }

    public function down()
    {
        Schema::table('aviso_clientes', function (Blueprint $table) {
            $table->dropColumn('type_id');
            $table->dropColumn('type');
        });
    }
}
