<?php

use App\Models\TipoCliente;
use Illuminate\Database\Seeder;

class TipoClienteSeeder extends Seeder
{

    public function run()
    {
    	TipoCliente::truncate();
        $items = [	
        	['Mixto','M'],
        	['Trabajador Autónomo','A'],
        	['Trabajador Dependiente','D']
		];

        foreach ($items as $item) {
        	TipoCliente::create([
        		'nombre' => trim($item[0]),
        		'sigla' => $item[1],
        	]);
        }
    }
}
