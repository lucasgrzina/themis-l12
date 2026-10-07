<?php

use App\Models\Area;
use Illuminate\Database\Seeder;

class AreasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	Area::truncate();
        $items = [
        	'Previsional',
        	'Laboral',
			'Civil',
			'Comercial',
			'Societario',
        ];

        foreach ($items as $item) {
        	Area::create([
        		'nombre' => $item,
        	]);
        }
    }
}
