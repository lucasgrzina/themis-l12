<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('time_horas', function (Blueprint $table) {
            $table->unsignedInteger('cliente_id')->nullable()->change();
            $table->unsignedInteger('gestion_id')->nullable()->change();
            // corriendo | pendiente (terminado sin vincular) | vinculada
            $table->string('estado', 20)->default('vinculada')->after('minutos')->index();
            $table->dateTime('inicio')->nullable()->after('estado');
            $table->dateTime('fin')->nullable()->after('inicio');
        });
    }

    public function down(): void
    {
        Schema::table('time_horas', function (Blueprint $table) {
            $table->dropColumn(['estado', 'inicio', 'fin']);
        });
    }
};
