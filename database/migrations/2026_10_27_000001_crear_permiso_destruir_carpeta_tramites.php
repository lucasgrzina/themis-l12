<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Agrega una operacion extra "Destruir carpeta" (DC) al permiso "Tramites",
 * al lado de las operaciones estandar C/R/U/D. Resulta en el permiso
 * `tramites:DC`, que gatea la marca MANUAL de "carpeta destruida" en el
 * informe de tramites historicos.
 *
 * Se hace por migracion idempotente (NO por PermissionsSeeder, que hace
 * Permission::truncate() y borraria todas las asignaciones rol<->permiso).
 * Se asigna al rol Administrador.
 */
return new class extends Migration
{
    private string $name = 'tramites:DC';

    public function up(): void
    {
        Permission::firstOrCreate(
            ['name' => $this->name, 'guard_name' => 'web'],
            ['display_name' => 'Destruir carpeta Tramites']
        );

        $admin = Role::where('name', 'Administrador')->where('guard_name', 'web')->first();
        if ($admin) {
            $admin->givePermissionTo($this->name);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::where('name', $this->name)->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
