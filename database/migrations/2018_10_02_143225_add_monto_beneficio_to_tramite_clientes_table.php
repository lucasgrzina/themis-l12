<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMontoBeneficioToTramiteClientesTable extends Migration
{
    public function up()
    {
        Schema::table('tramite_clientes', function (Blueprint $table) {
            $table->string('monto_beneficio')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tramite_clientes', function (Blueprint $table) {
            $table->dropColumn('monto_beneficio');
        });
    }
}
