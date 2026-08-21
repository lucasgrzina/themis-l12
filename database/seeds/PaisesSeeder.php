<?php

use App\Models\Pais;
use Illuminate\Database\Seeder;

class PaisesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	Pais::truncate();
        $items = [
        	[6,'Argentina'],
        	[7,'Uruguay'],
			[8,'Brasil'],
        ];

        foreach ($items as $item) {
        	Pais::create([
                'id' => $item[0],
        		'nombre' => $item[1],
        	]);
        }
    }
}
