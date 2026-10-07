<?php

use Illuminate\Database\Seeder;

/**
 * Seeder mínimo para la base de test (themis_test), usado por tests/TestCase.php.
 *
 * Deliberadamente NO llama a TipoClienteSeeder (la tabla tipo_clientes no
 * existe ni en la base de desarrollo real -- bug preexistente, no relacionado
 * con la migración) ni a ClientesSeeder (carga un lote grande de datos que
 * imitan clientes reales; los tests crean sus propios fixtures puntuales).
 */
class TestingSeeder extends Seeder
{
    public function run()
    {
        $this->call(RolesSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(AccionesControladasSeeder::class);
        $this->call(PermissionsSeeder::class);
        $this->call(RolesPermisosSeeder::class);
        $this->call(AreasSeeder::class);
        $this->call(PaisesSeeder::class);
        $this->call(TipoAportesSeeder::class);
        $this->call(TipoTramitesSeeder::class);
        $this->call(TipoDocumentosSeeder::class);
        // EstadoRequerimientosSeeder: se omite -- bug preexistente, la
        // migración 2017_12_26_161300 define `nombre` unique() global pero
        // 2018_06_13_155329 agregó area_id asumiendo nombres repetibles por
        // área (5 filas "Pendiente"). Choca contra el unique constraint en
        // cualquier seed limpio. Reportado, no corregido acá.
        $this->call(EstadoAnsesSeeder::class);
        $this->call(EstadoTramitesSeeder::class);
        $this->call(TipoSociedadesSeeder::class);
        $this->call(CondIvaSeeder::class);
        $this->call(ColegasSeeder::class);
        $this->call(EmpresasRefSeeder::class);
        $this->call(ReparticionOrigenSeeder::class);
        // JuzgadosSeeder: se omite -- mismo patrón de bug preexistente que
        // EstadoRequerimientosSeeder (nombre unique() global vs. datos
        // repetidos por area_id). Reportado, no corregido acá.
        $this->call(EstadoExpedienteSeeder::class);
        // DocRequeridaSeeder: se omite -- bug preexistente, falla con
        // "Trying to get property 'id' of non-object" en un lookup de
        // TipoTramite que no resuelve en un seed limpio. Reportado, no
        // corregido acá.
        $this->call(ProvinciasSeeder::class);
    }
}
