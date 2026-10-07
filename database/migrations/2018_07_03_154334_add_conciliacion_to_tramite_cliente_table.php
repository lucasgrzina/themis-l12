<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConciliacionToTramiteClienteTable extends Migration
{
    public function up()
    {
        Schema::table('tramite_expedientes', function (Blueprint $table) {
            $table->boolean('juicio_conciliado')->default(null);
            $table->string('nro_conciliacion', 50)->nullable();
            $table->date('fecha_conciliacion')->nullable();
        });
    }

    public function down()
    {
        Schema::table('tramite_expedientes', function (Blueprint $table) {
            $table->dropColumn('juicio_conciliado');
            $table->dropColumn('nro_conciliacion');
            $table->dropColumn('fecha_conciliacion');
        });
    }
}
