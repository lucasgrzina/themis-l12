<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutosParteToRequerimientosTable extends Migration
{
    public function up()
    {
        Schema::table('requerimiento_clientes', function (Blueprint $table) {
            $table->string('autos')->nullable();
            $table->char('parte',1)->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('requerimiento_clientes', function (Blueprint $table) {
            $table->dropColumn('autos');
            $table->dropColumn('parte');
        });
    }
}
