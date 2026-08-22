<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\UserArea;
use App\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Caracterización de la doble capa de autorización (Fase 00 del plan de
 * migración a Laravel 12): spatie/laravel-permission (roles/permisos) y
 * membresía de área propia (user_area). El bump 2 -> 8 de spatie
 * (Fase 05) es uno de los puntos de mayor riesgo del plan -- este test
 * es la referencia de comportamiento contra la que comparar.
 */
class AuthorizationTest extends TestCase
{
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $login = $this->postJson('/api/authenticate', [
            'username' => 'wavecom',
            'password' => 'wavecom',
        ]);
        $this->token = $login->json()['token'];
    }

    protected function auth(array $headers = [])
    {
        return array_merge(['Authorization' => "Bearer {$this->token}"], $headers);
    }

    public function test_asignar_rol_otorga_los_permisos_del_rol()
    {
        $role = Role::create(['name' => 'rol-de-test']);
        $permission = Permission::create(['name' => 'permiso-de-test']);
        $role->givePermissionTo($permission);

        $user = User::find(1);
        $this->assertFalse($user->hasPermissionTo('permiso-de-test'));

        $user->assignRole($role);

        $this->assertTrue($user->fresh()->hasPermissionTo('permiso-de-test'));
        $this->assertTrue($user->hasRole('rol-de-test'));
    }

    public function test_quitar_rol_quita_los_permisos_heredados()
    {
        $role = Role::create(['name' => 'rol-temporal']);
        $permission = Permission::create(['name' => 'permiso-temporal']);
        $role->givePermissionTo($permission);

        $user = User::find(1);
        $user->assignRole($role);
        $this->assertTrue($user->fresh()->hasPermissionTo('permiso-temporal'));

        $user->removeRole($role);

        $this->assertFalse($user->fresh()->hasPermissionTo('permiso-temporal'));
    }

    public function test_roles_full_devuelve_array_crudo_no_el_contrato_success_data_message()
    {
        Role::create(['name' => 'rol-para-listar']);

        $response = $this->getJson('/api/roles/full', $this->auth());

        $response->assertStatus(200);
        // A diferencia del resto de la API, este endpoint NO pasa por
        // sendResponse() -- devuelve el array de roles directo. El
        // frontend que arma la directiva v-can depende de esta forma
        // específica; si el bump de spatie 2->8 cambia la forma de
        // toArray() de Role/Permission, esto se rompe silenciosamente.
        $body = $response->json();
        $this->assertArrayNotHasKey('success', $body);
        $this->assertArrayNotHasKey('data', $body);
        $this->assertTrue(collect($body)->contains(function ($rol) {
            return $rol['name'] === 'rol-para-listar';
        }));
    }

    public function test_combo_responsables_filtra_por_area_responsable_y_visible()
    {
        $area = Area::first();
        $this->assertNotNull($area, 'Requiere al menos un área seedeada');

        $responsableEnElArea = User::create([
            'name' => 'Responsable En Area',
            'email' => 'responsable-test@nomail.com',
            'username' => 'responsable-test',
            'password' => bcrypt('x'),
            'responsable' => true,
            'visible' => true,
        ]);
        UserArea::create(['user_id' => $responsableEnElArea->id, 'area_id' => $area->id, 'responsable' => true]);

        $noResponsableEnElArea = User::create([
            'name' => 'No Responsable En Area',
            'email' => 'no-responsable-test@nomail.com',
            'username' => 'no-responsable-test',
            'password' => bcrypt('x'),
            'responsable' => false,
            'visible' => true,
        ]);
        UserArea::create(['user_id' => $noResponsableEnElArea->id, 'area_id' => $area->id, 'responsable' => false]);

        $responsableEnOtraArea = User::create([
            'name' => 'Responsable En Otra Area',
            'email' => 'responsable-otra-area@nomail.com',
            'username' => 'responsable-otra-area',
            'password' => bcrypt('x'),
            'responsable' => true,
            'visible' => true,
        ]);
        $otraArea = Area::where('id', '!=', $area->id)->first();
        UserArea::create(['user_id' => $responsableEnOtraArea->id, 'area_id' => $otraArea->id, 'responsable' => true]);

        $response = $this->getJson("/api/combos/responsables/{$area->id}", $this->auth());

        $response->assertStatus(200);
        $nombres = collect($response->json()['data'])->pluck('name');

        $this->assertTrue($nombres->contains('Responsable En Area'));
        $this->assertFalse($nombres->contains('No Responsable En Area'));
        $this->assertFalse($nombres->contains('Responsable En Otra Area'));
    }

    public function test_endpoint_de_negocio_no_exige_ningun_permiso_server_side()
    {
        // Hallazgo, no aspiración: hoy ningún controlador de la API llama
        // a hasPermissionTo()/can()/Gate::/role: ni permission: middleware
        // (verificado por grep sobre app/Http/Controllers/API y
        // routes/api.php). Un usuario autenticado sin ningún rol ni
        // permiso puede operar cualquier endpoint de negocio -- la
        // directiva v-can y los getters del store de Vuex son
        // cosméticos del lado del cliente, tal como documenta CLAUDE.md.
        // Este test congela ese hecho para que el bump de spatie 2->8
        // (Fase 05) no lo cambie sin que se note.
        $sinPermisos = User::create([
            'name' => 'Sin Permisos',
            'email' => 'sin-permisos@nomail.com',
            'username' => 'sin-permisos',
            'password' => bcrypt('x'),
        ]);

        $login = $this->postJson('/api/authenticate', [
            'username' => 'sin-permisos',
            'password' => 'x',
        ]);
        $token = $login->json()['token'];

        $response = $this->postJson('/api/clientes', [
            'personeria' => 'H',
            'nombre_completo' => 'Cliente Creado Sin Permisos',
            'sexo' => 'M',
            'tipo_doc_id' => 38,
            'nro_doc' => (string) random_int(10000000, 99999999),
        ], ['Authorization' => "Bearer {$token}"]);

        $response->assertStatus(200);
    }
}
