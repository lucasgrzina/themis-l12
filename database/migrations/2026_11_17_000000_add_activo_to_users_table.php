<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActivoToUsersTable extends Migration
{
    /**
     * Agrega la marca "activo" para poder dar de baja logica a usuarios que
     * ya no trabajan en el estudio sin borrarlos (se conserva el historial).
     * Es un concepto independiente de "visible" (que oculta del listado).
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('activo')->default(true);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }
}
