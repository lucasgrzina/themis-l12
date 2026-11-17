<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permiso extra del modulo Usuarios:
 *  - usuarios:NT -> habilita el combo "Time: Nivel" en el alta/modificacion de usuarios.
 *
 * El modulo "Usuarios" (nombre_permiso = usuarios) ya existe en acciones_controladas
 * (AccionesControladasSeeder), por eso aca solo se crea el permiso.
 *
 * Migracion idempotente (NO por PermissionsSeeder, que hace Permission::truncate()).
 * Se asigna al rol Administrador; el resto se reparte desde el ABM de Roles.
 */
return new class extends Migration
{
    private string $permiso = 'usuarios:NT';

    public function up(): void
    {
        Permission::firstOrCreate(
            ['name' => $this->permiso, 'guard_name' => 'web'],
            ['display_name' => 'Nivel en Time Usuarios']
        );

        $admin = Role::where('name', 'Administrador')->where('guard_name', 'web')->first();
        if ($admin) {
            $admin->givePermissionTo($this->permiso);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::where('name', $this->permiso)->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
