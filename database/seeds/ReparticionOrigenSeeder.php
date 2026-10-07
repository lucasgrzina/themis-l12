<?php

use App\Models\ReparticionOrigen;
use Illuminate\Database\Seeder;

class ReparticionOrigenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	ReparticionOrigen::truncate();
        $items = [	
			[67,'ANSES (UAP)                                       ',1],
			[68,'ANSES (UDAI CENTRO)                               ',1],
			[69,'ANSES (UDAI MONSERRAT)                            ',1],
			[529,'ANSES (UDAI MONTE GRANDE)                         ',1],
			[530,'ANSES (UDAI LANUS)                                ',1],
			[531,'ORIGENES                                          ',0],
			[532,'CONSOLIDAR                                        ',0],
			[533,'MAXIMA                                            ',0],
			[535,'PRORENTA                                          ',0],
			[536,'PREVISOL                                          ',0],
			[537,'MET                                               ',0],
			[538,'UNIDOS                                            ',0],
			[539,'NACION                                            ',0],
			[540,'PROFESION + AUGE                                  ',0],
			[541,'ARAUCA BIT                                        ',0],
			[746,'UCADEP',1],
			[747,'ANSES ARCHIVO SAN MARTIN                          ',1],
			[764,'ANSES (CONVENIOS INTERNACIONALES)                 ',1],
			[765,'ANSES (UDAI AVELLANEDA)                           ',1],
			[847,'ANSES (UDAI LOMAS DE ZAMORA)                      ',1],
			[849,'ANSES (UDAI MUNRO)                                ',1],
			[1344,'FUTURA AFJP                                       ',0],
			[1819,'ANSES (UDAI MORON)',1],
			[1820,'ANSES (UDAI URQUIZA)',1],
			[2124,'ANSES (UDAI QUILMES)',1],
			[2190,'ANSES (UDAI PACÍFICO)',1],
			[2573,'ANSES (UDAI PLAZA DE MAYO)',1],
			[2859,'ANSES (UDAI FLORES)',1],
			[2860,'ANSES (UDAI LINIERS)',1],
			[2964,'ANSES (WEB)',1],
			[3967,'ANSES (CAPITALIZACION)',1],
			[4053,'ANSES (UDAI SAN MIGUEL)',1],
			[4920,'ANSES (UDAI LONGCHAMPS)',1],
			[5339,'ANSES (UDAI MENDOZA)',1],
			[5644,'IPS',1],
			[5732,'ANSES (UDAI CIPOLLETTI)',1],
			[5999,'ANSES (OFICIOS JUDICIALES)',1],
			[6468,'ANSES (UDAI SAN VICENTE)',1],
			[6911,'ANSES (UDAI LAFERRERE)',1],
			[7208,'ANSES (UDAI FLORESTA)',1],
			[7264,'ANSES (UDAI ESCOBAR)',1],
			[7267,'ANSES (UDAI TURDERA)',1],
			[7282,'ANSES (UDAI NEUQUEN)',1],
			[7285,'ANSES (UDAI SAN CRISTOBAL)',1],
			[7290,'ANSES (UDAI SAN JUSTO)',1],
			[7295,'ANSES (UDAI BERAZATEGUI)',1],
			[7296,'ANSES (UDAI VILLA LUGANO)',1],
			[7305,'ANSES (UDAI BERAZATEGUY)',1],
			[7307,'ANSES (UDAI BARRACAS)',1],
			[7309,'ANSES (UDAI SAN FRANCISCO SOLANO)',1],
			[7312,'ANSES (UDAI EZEIZA)',1],
			[7320,'ANSES (UDAI TIGRE)',1],
			[7330,'ANSES (UDAI SAN ISIDRO)',1],
			[7333,'ANSES (UDAI LA PLATA II)',1],
			[7349,'ANSES (UDAI PILAR)',1],
			[7376,'ANSES <UDAI CAMPANA)',1],
			[7387,'ANSES (UDAI SAN MARTIN)',1],
			[7396,'ANSES (UDAI ITUZAINGO)',1],
			[7397,'ANSES (UDAI CRUZ DEL EJE)',1],
			[7403,'ANSES (UDAI VILLA ALBERTINA)',1],
			[7407,'ANSES (UDAI TRES DE FEBRERO)',1],
			[7412,'ANSES (UDAI CAÑUELAS)',1],
			[7436,'ANSES (UDAI SAN CARLOS DE BARILOCHE)',1],
			[7440,'ANSES (UDAI JUNIN)',1],
			[7517,'ANSES (UDAI MERLO)',1],
			[7661,'ANSES (UDAI CURUZU CUATIA)',1],
			[7662,'ANSES (UDAI SAN FERNANDO)',1],
			[7683,'ANSES (UDAI FLORENCIO VARELA)',1],
			[7713,'ANSES (UDAI RAMOS MEJIA)',1],
			[7714,'ANSES (UDAI COMODORO RIVADAVIA)',1],
			[7995,'ANSES (UDAI FLORES II)',1],

		];

        foreach ($items as $item) {
        	ReparticionOrigen::create([
        		'id' => $item[0],
        		'nombre' => trim($item[1]),
        		'vigente' => $item[2]
        	]);
        }
    }
}
