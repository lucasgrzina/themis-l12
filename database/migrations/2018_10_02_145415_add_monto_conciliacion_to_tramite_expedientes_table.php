<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMontoConciliacionToTramiteExpedientesTable extends Migration
{
    public function up()
    {
        Schema::table('tramite_expedientes', function (Blueprint $table) {
            $table->string('monto_conciliacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tramite_expedientes', function (Blueprint $table) {
            $table->dropColumn('monto_conciliacion');
        });
    }
}
