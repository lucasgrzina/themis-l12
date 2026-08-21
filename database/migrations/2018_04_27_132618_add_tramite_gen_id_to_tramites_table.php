<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTramiteGenIdToTramitesTable extends Migration
{
    public function up()
    {
        Schema::table('tramite_clientes', function (Blueprint $table) {
            $table->unsignedInteger('tramite_sig_id')->nullable()->index();
            $table->unsignedInteger('tramite_ant_id')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('tramite_clientes', function (Blueprint $table) {
            $table->dropColumn('tramite_sig_id');
            $table->dropColumn('tramite_ant_id');
        });
    }
}
