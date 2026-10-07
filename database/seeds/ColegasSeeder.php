<?php
use App\Models\Colega;
use Illuminate\Database\Seeder;

class ColegasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	Colega::truncate();
        $items = [	
			[3346,'Mónica Modesto','','','156555-1716',1],
			[3682,'Deleg linea 17','','','',0],
			[3749,'German','','','',0],
			[3864,'Graciela Algarrobo','','','',0],
			[3865,'Raul Cardozo','Agustín de Vedia 2206 Capital (hija)','delegado sindicato vialidad nacional','49210898 (hija Eva Malvino)  1566496414 (cardozo)',0],
			[3904,'Facurndo Etcheverry','','','52378820',1],
			[3974,'Eduardo Umpierez (L 179)','','','',1],
			[3975,'Patricia Altieri','','','',1],
			[3986,'DR.HUNT PATRICIO','CONOCIDO DE MIGUEL','','4294-5200 D.18 HS.',0],
			[4219,'Eisenack Rosana','','','',1],
			[4545,'Norma Castellanos','','','',0],
			[4561,'Paiva Martín','','','',1],
			[4614,'Mary','','','',0],
			[4901,'Alicia Cabral','','','',0],
			[4902,'Gesualdo Gabriel','','','',1],
			[5315,'Silvio de Ronda','','','',0],
			[5686,'angelica','','','',0],
			[5769,'Jose Aureano','','','',1],
			[5908,'Walter de Esisa','','','',0],
			[5909,'Marcos P','','','',0],
			[6039,'Romina Schilling','','','',0],
			[6142,'Fabian Hilal','','','',1],
			[6143,'Marta Bayer','','','',1],
			[6419,'Dra. Calvo','en el edificio','','',0],
			[6436,'KARINA LARRIBA','','','4280 0717',0],
			[6498,'kelsey','','','',0],
			[6592,'Marti Claudia','abogada conocida dino campos','','',0],
			[6672,'Pedicino Lidia','','','',0],
			[6673,'Guillermo Castellanos','','','',0],
			[6766,'Cecilia Rodriguez','56 numero 516 1 B','La Plata','0221 15 5432222',1],
			[6912,'Estudio Fazzolari Huarte Formaro Gonzalez','La Rioja 626','(8300) NEUQUEN','0299 4431069',1],
			[7002,'GIUNTA VICTOR','','','4788-0385/15-5639-1816',0],
			[7124,'Santa Maria Park','','','6261 5815',1],
			[7148,'YOLANDA','','','',0],
			[7180,'error','','','',0],
			[8213,'Felix Gaibisso','','','',1] 	
		];

        foreach ($items as $item) {
        	Colega::create([
        		'id' => $item[0],
        		'nombre' => trim($item[1]),
        		'direccion' => trim($item[2]),
        		'localidad' => trim($item[3]),
        		'telefono' => trim($item[4]),
        		'vigente' => $item[5]
        	]);
        }
    }
}
