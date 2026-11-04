<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Participacion (% de honorarios) de cada abogado en cada cliente.
 *
 * - periodo NULL = participacion por DEFECTO (se usa en cualquier mes sin override).
 * - periodo seteado (YYYY-MM-01) = OVERRIDE para ese mes puntual.
 * porcentaje se guarda como fraccion (0.05 = 5%).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_participaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cliente_id')->index();
            $table->unsignedInteger('user_id')->index();
            $table->decimal('porcentaje', 7, 4)->default(0);
            $table->date('periodo')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['cliente_id', 'user_id', 'periodo'], 'time_participaciones_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_participaciones');
    }
};
