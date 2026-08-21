<?php

use App\Models\EstadoRequerimiento;
use Illuminate\Database\Seeder;

class EstadoRequerimientosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	EstadoRequerimiento::truncate();
        $items = [	
			[49,'En Análisis',0,1],
			[50,'A Espera de F.A.D.',1,1],
			[51,'Pendiente',1,1],
			[52,'Cumplido Parcial',0,1],
			[53,'Cumplido Parcial (con Turno)',0,1,false],
			[54,'Cumplido',1,1,false],
			[524,'Pendiente con Turno',1,1],
			[1823,'Validación de pagos',0,1],
			[1851,'Espera doc. titular',1,1],
			[2064,'Carpeta suspendida',1,1],
			[2753,'Concluido/Archivado',1,1],
			[3002,'A espera de SIJP',1,1],
			[3003,'A espera de clave fiscal',1,1],        	
			[3004,'A espera de clave seg. social',1,1],        	
            
            [3005,'Pendiente',1,2,false],
            [3006,'Pendiente',1,3,false],
            [3007,'Pendiente',1,4,false],
            [3008,'Pendiente',1,5,false],

            [3009,'Cumplido',1,2,false],
            [3010,'Cumplido',1,3,false],
            [3011,'Cumplido',1,4,false],
            [3012,'Cumplido',1,5,false],

		];

        foreach ($items as $item) {
        	EstadoRequerimiento::create([
        		'id' => $item[0],
        		'nombre' => trim($item[1]),
        		'vigente' => $item[2],
                'area_id' => $item[3],
                'modificable' => (isset($item[4]) ? $item[4] : true)
        	]);
        }
    }
}
