<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Valor hora por cliente y categoria de abogado.
 *
 * - categoria NULL = tarifa BASE del cliente (se aplica cuando el abogado no
 *   tiene una tarifa especifica para su categoria). Ej. AGRO = una sola tarifa base.
 * - categoria seteada = tarifa especifica (ej. SAVARY: Abono/Sr/Semi Sr/Director).
 * - vigencia_desde permite historizar cambios de valor hora. Se toma la vigencia
 *   mas reciente <= periodo del informe.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_tarifas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cliente_id')->index();
            $table->string('categoria', 50)->nullable();
            $table->decimal('valor_hora', 14, 2)->default(0);
            $table->string('moneda', 3)->default('USD');
            $table->date('vigencia_desde')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cliente_id', 'categoria', 'vigencia_desde'], 'time_tarifas_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_tarifas');
    }
};
