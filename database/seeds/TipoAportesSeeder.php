<?php

use App\Models\TipoAporte;
use Illuminate\Database\Seeder;

class TipoAportesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	TipoAporte::truncate();
        $items = [
        	[36,'Trabajador Autónomo'],
        	[37,'Trabajador Dependiente'],
			[106,'Mixto'],
        ];

        foreach ($items as $item) {
        	TipoAporte::create([
                'id' => $item[0],
        		'nombre' => $item[1],
        	]);
        }
    }
}
