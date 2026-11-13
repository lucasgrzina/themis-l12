<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Participaciones de honorarios a COLABORADORES EXTERNOS (no-usuarios del sistema).
 *
 * A diferencia de time_participaciones (ligada a un user_id y al modelo 'horas'),
 * estas se aplican sobre la liquidacion por PRESUPUESTO y van identificadas por
 * nombre libre (ej. "Rosenblat", "Colaborador CVA").
 *
 * - base: sobre que monto se calcula el porcentaje.
 *     'subtotal'      = subtotal sin IVA, se descuenta ANTES del Subtotal neto.
 *     'subtotal_neto' = subtotal neto, se descuenta DESPUES (sobre el neto).
 * - orden: desempate dentro de la misma base.
 * - periodo NULL = por defecto; periodo seteado (YYYY-MM-01) = override mensual.
 * porcentaje se guarda como fraccion (0.20 = 20%).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_participaciones_externas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cliente_id')->index();
            $table->string('nombre', 150);
            $table->decimal('porcentaje', 7, 4)->default(0);
            $table->enum('base', ['subtotal', 'subtotal_neto'])->default('subtotal');
            $table->unsignedInteger('orden')->default(0);
            $table->date('periodo')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['cliente_id', 'nombre', 'base', 'periodo'], 'time_part_ext_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_participaciones_externas');
    }
};
