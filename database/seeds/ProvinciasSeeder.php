<?php

use App\Models\Provincia;
use Illuminate\Database\Seeder;

class ProvinciasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	Provincia::truncate();
        $items = [
			[7,'Buenos Aires'],
			[9,'Capital Federal'],
			[8,'Catamarca'],
			[12,'Chaco'],
			[13,'Chubut'],
			[10,'Cordoba'],
			[11,'Corrientes'],
			[14,'Entre Ríos'],
			[15,'Formosa'],
			[17,'La Pampa'],
			[18,'La Rioja'],
			[19,'Mendoza'],
			[20,'Misiones'],
			[21,'Neuquen'],
			[22,'Río Negro'],
			[28,'Salta'],
			[24,'San Juan'],
			[25,'San Luis'],
			[26,'San Miguel de Tucumán'],
			[16,'San Salvador de Jujuy'],
			[29,'Santa Cruz'],
			[27,'Santa Fe'],
			[23,'Santiago del Estero'],
			[30,'Tierra del Fuego'],
        ];

        foreach ($items as $item) {
        	Provincia::create([
        		'id' => $item[0],
        		'nombre' => $item[1],
        	]);
        }
    }
}
