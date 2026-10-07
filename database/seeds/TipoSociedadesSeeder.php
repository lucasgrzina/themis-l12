<?php

use App\Models\TipoSociedad;
use Illuminate\Database\Seeder;

class TipoSociedadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	TipoSociedad::truncate();
        $items = [
        	'S.R.L.',
        	'S.A.',
			'Sociedad Comercial',
			'Fideicomiso',
			'Otros'

        ];

        foreach ($items as $item) {
        	TipoSociedad::create([
        		'nombre' => $item,
        	]);
        }
    }
}
