<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCanChangeToEstTramiteTable extends Migration
{
    public function up()
    {
        Schema::table('estado_tramites', function (Blueprint $table) {
            $table->boolean('modificable')->default(true);
        });
    }

    public function down()
    {
        Schema::table('estado_tramites', function (Blueprint $table) {
            $table->dropColumn('modificable');
        });
    }
}
