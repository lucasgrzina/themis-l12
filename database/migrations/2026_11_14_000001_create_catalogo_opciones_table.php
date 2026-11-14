<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Catalogo generico de opciones para el wizard de Alta rapida de clientes.
 * Una sola tabla con "grupo":
 *   - motivo_aviso   : motivos predefinidos de aviso
 *   - tipo_contacto  : descripcion de telefonos / emails
 *   - tipo_documento : nombre de los documentos adjuntos
 * En la UI cada lista suma la opcion "Otro" (texto libre), que NO se guarda aca.
 *
 * Tambien registra el permiso opciones-de-alta:C/R/U/D (ABM del catalogo),
 * idempotente y asignado al rol Administrador (igual que los permisos de Time).
 */
return new class extends Migration
{
    private string $slug = 'opciones-de-alta';
    private string $nombreModulo = 'Opciones de alta rapida';

    public function up(): void
    {
        Schema::create('catalogo_opciones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('grupo', 40)->index();
            $table->string('nombre', 150);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('vigente')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        $now = now();
        $seed = [
            'motivo_aviso' => [
                'Alta de cliente: completar ficha',
                'Alta de cliente: revisar documentación',
                'Primera entrevista',
                'Seguimiento',
            ],
            'tipo_contacto' => ['Celular', 'Particular', 'Laboral'],
            'tipo_documento' => ['DNI', 'Constancia de CUIT/CUIL', 'Poder', 'Estatuto / Contrato social', 'Acta de designación'],
        ];
        foreach ($seed as $grupo => $nombres) {
            foreach ($nombres as $i => $nombre) {
                DB::table('catalogo_opciones')->insert([
                    'grupo' => $grupo, 'nombre' => $nombre, 'orden' => $i + 1,
                    'vigente' => true, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        if (!DB::table('acciones_controladas')->where('nombre_permiso', $this->slug)->exists()) {
            DB::table('acciones_controladas')->insert([
                'nombre' => $this->nombreModulo,
                'nombre_permiso' => $this->slug,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        $admin = Role::where('name', 'Administrador')->where('guard_name', 'web')->first();
        foreach (['C', 'R', 'U', 'D'] as $op) {
            Permission::firstOrCreate(
                ['name' => "{$this->slug}:$op", 'guard_name' => 'web'],
                ['display_name' => "$op {$this->nombreModulo}"]
            );
            if ($admin) {
                $admin->givePermissionTo("{$this->slug}:$op");
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogo_opciones');
        Permission::where('name', 'like', "{$this->slug}:%")->delete();
        DB::table('acciones_controladas')->where('nombre_permiso', $this->slug)->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
