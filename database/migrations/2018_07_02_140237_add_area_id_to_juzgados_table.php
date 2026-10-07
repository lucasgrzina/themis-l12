<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAreaIdToJuzgadosTable extends Migration
{
    public function up()
    {
        Schema::table('juzgados', function (Blueprint $table) {
            $table->unsignedInteger('area_id')->nullable()->index();
            $table->unique(['nombre', 'area_id']);
        });
    }

    public function down()
    {
        Schema::table('juzgados', function (Blueprint $table) {
            $table->dropColumn('area_id');
        });
    }
}
