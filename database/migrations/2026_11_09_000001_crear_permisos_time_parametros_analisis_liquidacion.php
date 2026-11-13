<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permisos (no niveles) para:
 *  - time-parametros:C/R/U/D  -> pantallas y APIs de Parametros de Time
 *                                (por cliente, categorias, tipo de cambio, retenciones)
 *  - time-analisis-de-horas:R -> informe "Analisis de horas" (todo el estudio)
 *  - time-liquidacion:R       -> informe "Liquidacion Cliente/Abogado" (imprimir y Excel)
 *
 * Migracion idempotente (NO por PermissionsSeeder, que hace Permission::truncate()).
 * Se asignan al rol Administrador; el resto se reparte desde el ABM de Roles.
 * Ademas registra los modulos en acciones_controladas para que aparezcan en ese ABM.
 */
return new class extends Migration
{
    /** nombre del modulo => [nombre_permiso, operaciones] */
    private array $modulos = [
        'Time Parametros'        => ['time-parametros',        ['C', 'R', 'U', 'D']],
        'Time Analisis de Horas' => ['time-analisis-de-horas', ['R']],
        'Time Liquidacion'       => ['time-liquidacion',       ['R']],
    ];

    public function up(): void
    {
        $admin = Role::where('name', 'Administrador')->where('guard_name', 'web')->first();

        foreach ($this->modulos as $nombre => [$slug, $ops]) {
            $existe = DB::table('acciones_controladas')->where('nombre_permiso', $slug)->exists();
            if (!$existe) {
                DB::table('acciones_controladas')->insert([
                    'nombre'         => $nombre,
                    'nombre_permiso' => $slug,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }

            foreach ($ops as $op) {
                Permission::firstOrCreate(
                    ['name' => "$slug:$op", 'guard_name' => 'web'],
                    ['display_name' => "$op $nombre"]
                );
                if ($admin) {
                    $admin->givePermissionTo("$slug:$op");
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        foreach ($this->modulos as [$slug, $ops]) {
            Permission::where('name', 'like', "$slug:%")->delete();
            DB::table('acciones_controladas')->where('nombre_permiso', $slug)->delete();
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
