<?php

use App\Models\TipoDocumento;
use Illuminate\Database\Seeder;

class TipoDocumentosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	TipoDocumento::truncate();
        $items = [
            [38,'D.N.I'],
            [39,'L.C.'],
            [40,'L.E.'],
            [41,'OTRO'],
            [108,'C.I.'],
            [490,'D.U.'],
            [491,'PASAPORTE'],
            [8271,'otro doc']
        ];

        foreach ($items as $item) {
        	TipoDocumento::create([
        		'id' => $item[0],
        		'nombre' => $item[1]
        	]);
        }
    }
}
