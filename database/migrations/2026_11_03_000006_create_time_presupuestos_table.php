<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Presupuesto / abono fijo mensual para clientes con modelo 'presupuesto'
 * (ej. SOLUCIONES GRAFICAS). No usa horas: parte de un monto fijo en $.
 *
 * periodo = primer dia del mes (YYYY-MM-01). subtotal = monto neto en $ (sin IVA).
 * aplica_iva a nivel presupuesto por si un mismo cliente mezcla items con/sin IVA.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_presupuestos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cliente_id')->index();
            $table->date('periodo')->index();
            $table->string('descripcion', 255)->nullable();
            $table->decimal('subtotal', 16, 2)->default(0);
            $table->boolean('aplica_iva')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_presupuestos');
    }
};
