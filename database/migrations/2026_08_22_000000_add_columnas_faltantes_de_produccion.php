<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estas columnas existen en la base de producción/dev del proyecto 5.5
 * (themis) pero nunca tuvieron una migración propia -- se agregaron en
 * algún momento directamente contra la base (fuera del control de
 * versiones de esquema). Detectado al correr el informe Excel de
 * trámites (InformesRepository::tramites()) contra una base migrada
 * desde cero: "Unknown column 'c.clave_seguridad_social'". Comparado
 * el esquema completo de la base real del 5.5 contra la migrada de
 * themis-l12 para encontrar el resto.
 *
 * fecha_vto en tramite_clientes es, a juzgar por el nombre del índice
 * heredado (tramite_clientes_fecha_vto_directorio_index), una columna
 * fecha_vto_directorio (creada por
 * 2018_05_23_144246_add_area_5_data_to_table_tramites.php) renombrada
 * directamente en la base sin migración -- el modelo TramiteCliente ya
 * referencia 'fecha_vto', no el nombre viejo.
 */
class AddColumnasFaltantesDeProduccion extends Migration
{
    public function up()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->date('fecha_casamiento')->nullable();
            $table->integer('anios_convivencia')->nullable()->default(0);
            $table->integer('hijos_comun')->nullable()->default(0);
            $table->date('fecha_enviudez')->nullable();
            $table->string('clave_seguridad_social', 100)->nullable();
            $table->string('clave_fiscal', 100)->nullable();
        });

        Schema::table('tramite_clientes', function (Blueprint $table) {
            $table->date('fecha_vto')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_casamiento',
                'anios_convivencia',
                'hijos_comun',
                'fecha_enviudez',
                'clave_seguridad_social',
                'clave_fiscal',
            ]);
        });

        Schema::table('tramite_clientes', function (Blueprint $table) {
            $table->dropColumn('fecha_vto');
        });
    }
}
