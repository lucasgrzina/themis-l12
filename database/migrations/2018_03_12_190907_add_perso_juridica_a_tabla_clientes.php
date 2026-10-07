<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPersoJuridicaATablaClientes extends Migration
{
    public function up()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->integer('tipo_sociedad_id')->nullable()->unsigned()->index();
            $table->string('sede_social',2000)->nullable();
            $table->string('nombre_rep_legal',200)->nullable();
            $table->text('directorio')->nullable();
            $table->date('fecha_vto_directorio')->nullable();
            $table->boolean('libros_estudio')->default(FALSE)->nullable();
            $table->string('dom_fiscal',1000)->nullable();
            $table->string('dom_legal',1000)->nullable();
            $table->text('actividad')->nullable();
            $table->text('facultades')->nullable();
            $table->integer('cond_iva_id')->nullable()->unsigned()->index();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn('tipo_sociedad_id');
            $table->dropColumn('sede_social');

            $table->dropColumn('nombre_rep_legal');
            $table->dropColumn('directorio');
            $table->dropColumn('fecha_vto_directorio');
            $table->dropColumn('libros_estudio');
            $table->dropColumn('dom_fiscal');
            $table->dropColumn('dom_legal');
            $table->dropColumn('actividad');
            $table->dropColumn('facultades');
            $table->dropColumn('cond_iva_id');

        });
    }
}
