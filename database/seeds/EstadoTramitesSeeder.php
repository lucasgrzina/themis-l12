<?php

use App\Models\EstadoTramite;
use Illuminate\Database\Seeder;

class EstadoTramitesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	EstadoTramite::truncate();
        $items = [	
			[55,'Iniciado                                          ',1,1],
			[56,'Citado AFJP                                       ',0,1],
			[512,'Citado ANSES                                      ',1,1],
			[513,'Citado por Cese (AFJP)                            ',0,1],
			[514,'Citado por Cese (ANSES)                           ',1,1],
			[515,'Beneficio Acordado (AFJP)                         ',0,1,false],
			[516,'Beneficio Acordado (ANSES)                        ',1,1,false],
			[517,'Denegado                                          ',1,1],
			[518,'CARSS                                             ',0,1],
			[519,'Error Material                                    ',0,1],
			[520,'Verificaciones                                    ',0,1],
			[521,'Analisis de Especialista (Legales)                ',0,1],
			[522,'Espera de Normativa                               ',0,1],
			[523,'Computos y Liquidación                            ',0,1],
			[525,'En espera de expediente de otra area              ',0,1],
			[745,'Resuelto desfavorablemente                        ',1,1,false],
			[761,'Remitido a Dictaminar                             ',0,1],
			[762,'Resuelto favorablemente                           ',1,1,false],
			[848,'En Espera de Examen Médico                        ',0,1],
			[1488,'Espera plazo renta vitalicia                      ',0,1],
			[5136,'UAP                                               ',0,1],
			[5137,'A espera dictamen CMP.',0,1],

			//SOCIETARIO
			[5138,'Ingresado',1,5],
			[5139,'Con vista',1,5],
			[5140,'En Estudio Precalificado',1,5],
			[5141,'En Proceso de Registración',1,5],
			[5142,'Archivado (IGJ)',1,5],
			[5143,'Concluido',1,5,false],

			//CIVIL
			[5144,'Ingresado',1,3],
			[5145,'Resuelto favorablemente',1,3,false],
			[5146,'Resuelto desfavorablemente',1,3,false],
				[5147,'Mediación',1,3,false],
			[5148,'Concluido',1,3,false],

			//COMERCIAL
			[5149,'Ingresado',1,4],
			[5150,'Resuelto favorablemente',1,4,false],
			[5151,'Resuelto desfavorablemente',1,4,false],
			[5152,'Mediación',1,4,false],
			[5153,'Concluido',1,4,false],		

			//LABORAL
			[5154,'Ingresado',1,2],
			[5155,'Seclo acordado',1,2,false],
			[5156,'Seclo sin acuerdo',1,2,false],
			[5157,'Juicio conciliado',1,2,false],
			[5158,'Concluido',1,2,false],				
		];

        foreach ($items as $item) {
        	EstadoTramite::create([
        		'id' => $item[0],
        		'nombre' => trim($item[1]),
        		'vigente' => $item[2],
        		'area_id' => $item[3],
        		'modificable' => (isset($item[4]) ? $item[4] : true)
        	]);
        }
    }
}
