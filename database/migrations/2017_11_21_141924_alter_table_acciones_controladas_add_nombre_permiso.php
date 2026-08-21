<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableAccionesControladasAddNombrePermiso extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('acciones_controladas', function (Blueprint $table) {
            $table->string('nombre_permiso',100)->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('acciones_controladas', function (Blueprint $table) {
            $table->dropColumn('nombre_permiso');
        });
    }
}
