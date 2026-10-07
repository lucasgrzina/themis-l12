<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPpalResponsableRequerimientosTable extends Migration
{
    public function up()
    {
        Schema::table('responsable_requerimientos', function (Blueprint $table) {
            $table->boolean('ppal')->default(false)->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('responsable_requerimientos', function (Blueprint $table) {
            $table->dropColumn('ppal');
        });
    }
}
