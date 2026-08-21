<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeceasedToTramiteClientesTable extends Migration
{
    public function up()
    {
        Schema::table('tramite_clientes', function (Blueprint $table) {
            $table->boolean('deceased')->default(false);
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
            $table->dropColumn('deceased');
        });
    }
}
