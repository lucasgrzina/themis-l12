<?php

use App\Models\EstadoAnses;
use Illuminate\Database\Seeder;

class EstadoAnsesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
    	EstadoAnses::truncate();
        $items = [
			['Todavía sin nada',1],
			['Con 473',1],
			['En ejecución',1],
			['En ejecución parcial',1],
			['Con 150 ley nueva',1],
			['Con 150 ley vieja',1],
			['Fallecidos con viuda sin ejecutar',1],
			['Fallecidos con viuda en ejecución',1],
			['Fallecidos en sucesión',1],
			['Fallecidos en sucesión con 464 generado',1],
			['No son reajustes (pensiones/retiros por invalidez ganados en juicio)',1],
			['Favorables',1],
			['Terminados',1],     	
		];

        foreach ($items as $item) {
        	EstadoAnses::create([
        		'nombre' => trim($item[0]),
        		'vigente' => $item[1],
        	]);
        }
    }

}
