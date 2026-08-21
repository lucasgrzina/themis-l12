<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
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
        $this->call(TipoClienteSeeder::class);
        $this->call(TipoDocumentosSeeder::class);
        $this->call(EstadoRequerimientosSeeder::class);
        $this->call(EstadoAnsesSeeder::class);
        $this->call(EstadoTramitesSeeder::class);
        $this->call(TipoSociedadesSeeder::class);
        $this->call(CondIvaSeeder::class);
        $this->call(ColegasSeeder::class);
        $this->call(EmpresasRefSeeder::class);
        $this->call(ReparticionOrigenSeeder::class);
        $this->call(JuzgadosSeeder::class);
        $this->call(EstadoExpedienteSeeder::class);
        $this->call(DocRequeridaSeeder::class);
        $this->call(ProvinciasSeeder::class);
        $this->call(ClientesSeeder::class);
        //$this->call(RequerimientoClienteSeeder::class);
        
    }
}
