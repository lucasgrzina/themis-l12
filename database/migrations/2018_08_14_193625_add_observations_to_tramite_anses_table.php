<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddObservationsToTramiteAnsesTable extends Migration
{
    public function up()
    {
        Schema::table('tramite_anses', function (Blueprint $table) {
            $table->string('observations',250)->nullable();
        });
    }

    public function down()
    {
        Schema::table('tramite_anses', function (Blueprint $table) {
            $table->dropColumn('observations');
        });
    }
}
