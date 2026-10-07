<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToDocumentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documento_clientes', function (Blueprint $table) {
            $table->string('nombre_real',200);
            $table->date('fecha_archivo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documento_clientes', function (Blueprint $table) {
            $table->dropColumn('nombre_real');
            $table->dropColumn('fecha_archivo');
        });
    }
}
