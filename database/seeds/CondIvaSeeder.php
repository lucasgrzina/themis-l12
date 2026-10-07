<?php

use App\Models\CondIva;
use Illuminate\Database\Seeder;

class CondIvaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	CondIva::truncate();
        $items = [
        	[1090,'Responsable Inscripto'],
        	[1091,'Excento'],
        ];

        foreach ($items as $item) {
        	CondIva::create([
        		'id' => $item[0],
                'nombre' => $item[1],
        	]);
        }
    }
}
