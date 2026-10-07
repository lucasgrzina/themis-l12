<?php

use App\Models\EstadoExpediente;
use Illuminate\Database\Seeder;

class EstadoExpedienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	EstadoExpediente::truncate();
        $items = [	
			[4580,'SORTEO                                            ',1],
			[4634,'CONTESTACION DE DEMANDA                           ',1],
			[4635,'PRUEBA                                            ',1],
			[4636,'ESPERA EXPEDIENTE ADMINISTRATIVO                  ',1],
			[4637,'SENTENCIA                                         ',1],
			[4638,'CAMARA                                            ',1],
			[4639,'CORTE                                             ',1],
			[4640,'PARA DEVOLVER A ANSES                             ',1],
			[4641,'CONCLUIDO                                         ',1],
			[4642,'EJECUCION                                         ',1]	
		];

        foreach ($items as $item) {
        	EstadoExpediente::create([
        		'id' => $item[0],
        		'nombre' => trim($item[1]),
        		'vigente' => $item[2]
        	]);
        }
    }
}
