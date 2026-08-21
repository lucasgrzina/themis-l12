<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateColegasTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('colegas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 150);
            $table->string('direccion')->nullable();
            $table->string('localidad', 100)->nullable();
            $table->string('telefono', 150)->nullable();
            $table->boolean('vigente')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('colegas');
    }
}
