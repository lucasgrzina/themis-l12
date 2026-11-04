<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modelo de facturacion del cliente para el informe de liquidacion del Time.
 *
 * - modelo_facturacion: 'horas' (liquida por horas cargadas x valor hora) o
 *   'presupuesto' (monto fijo/abono mensual, sin horas; ej. SOLUCIONES GRAFICAS).
 * - aplica_iva: si al cliente se le adiciona IVA (tipico del modelo presupuesto).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->enum('modelo_facturacion', ['horas', 'presupuesto'])
                  ->default('horas')
                  ->after('categoria');
            $table->boolean('aplica_iva')->default(false)->after('modelo_facturacion');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['modelo_facturacion', 'aplica_iva']);
        });
    }
};
