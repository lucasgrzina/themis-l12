<?php

use App\Models\AccionesControladas;
use Illuminate\Database\Seeder;

class AccionesControladasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	AccionesControladas::truncate();
        $acciones = [
        	'Usuarios',
        	'Roles',
			'Clientes',
			'Tipos de Aporte',
			'Paises',
			'Tipos de Documento',
			'Areas',
			'Tipos de Tramite',
			'Tipos de Cliente',
			'Estados de Requerimiento',
			'Estados de Tramite',
			'Estados de Anses',
			'Tipos de Sociedades',
			'Cond. IVA',
			'Colegas',
			'Empresas de Referencia',
			'Reparticiones de Origen',
			'Juzgados',
			'Estados de Expediente',
			'Documentacion Requerida',
			'Tramites',
			'Requerimientos',
			'Consulta de informes',
			'Documentos',
			'Avisos',
			'Time',
/*			'Parametros de Operación',
			'Categorias de Cliente',
			'Tipos de Documento',
			'Recomendados',
			'Empresas de Referencia',
			
			'Estados de Requerimiento',
			'Consulta de informes',
			'Administracion de Parametros',
			'Abogados Responsables',
			'Reparticiones de Origen',
			'Estados de Tramites',
			'Archivar Tramites',
			'Oficinas',
			'Documentacion Requerida',
			'Posicion de IVA',
			'Tipo Observaciones',
			'Beneficios',
			'Observaciones',
			'Administracion de Clientes',
			'Ventas',
			'Cobros',
			'Recomendado por'        	*/
        ];

        foreach ($acciones as $accion) {
        	AccionesControladas::create([
        		'nombre' => $accion,
        		'nombre_permiso' => \Illuminate\Support\Str::slug($accion)
        	]);
        }
    }
}
