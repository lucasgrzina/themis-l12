<?php

use App\Models\TipoTramite;
use Illuminate\Database\Seeder;

class TipoTramitesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	TipoTramite::truncate();
        $items = [	[42,'Jubilacion Ordinaria Ley 18037/8                  ',1,1,null],
					[43,'PBU-PC-PAP                                        ',1,1,null],
					[44,'PBU-PC-JO (Capitalización)                        ',0,1,null],
					[45,'Pensión Directa (Reparto)                         ',1,1,'PENSION'],
					[46,'Pension Directa (Capitalización)                  ',0,1,'PENSION'],
					[47,'Pensión Derivada (Reparto)                        ',1,1,'PENSION'],
					[48,'Pensión Derivada (Capitalización)                 ',0,1,'PENSION'],
					[492,'Pensión Derivada de Edad Avanzada (Reparto)       ',0,1,'PENSION'],
					[493,'Pensión Derivada de Edad Avanzada (Capitalización)',0,1,'PENSION'],
					[494,'Pensión (Validación)                              ',0,1,'PENSION'],
					[495,'Retiro Transitorio Invalidez (Reparto)            ',1,1,null],
					[496,'Retiro Transitorio Invalidez (Capitalización)     ',0,1,null],
					[497,'Retiro Transitorio Invalidez (validación)         ',0,1,null],
					[498,'RTI derivado de Avanzada (Reparto)                ',0,1,null],
					[499,'RTI derivado de Avanzada (Capitalización)         ',0,1,null],
					[500,'Retiro Definitivo Invalidez (Reparto)             ',1,1,null],
					[501,'Retiro Definitivo Invalidez (Capitalización)      ',0,1,null],
					[502,'Prestación por Edad Avanzada (Reparto)            ',1,1,null],
					[503,'Prestación por Edad Avanzada (Capitalización)     ',0,1,null],
					[504,'Reconocimiento de Servicios (Reparto)             ',1,1,null],
					[505,'Reconocimiento de Servicios (Capitalización)      ',0,1,null],
					[506,'Reconocimiento de Servicios (IPS)                 ',1,1,null],
					[507,'Reconocimiento de Servicios (PFA)                 ',1,1,null],
					[508,'Reconocimiento de Servicios (Caja Profesional)    ',1,1,null],
					[509,'Reajuste por Movilidad de Haberes                 ',1,1,null],
					[510,'Reajuste por Error Material                       ',1,1,null],
					[511,'Acrecimiento de Pensión                           ',1,1,null],
					[742,'RECURSO CARSS                                     ',1,1,null],
					[763,'Error Material                                    ',1,1,null],
					[1025,'Asignacion familiar                               ',1,1,null],
					[1134,'INSTITUTO PREVISION SOCIAL BS.AS.                 ',1,1,null],
					[1264,'HABER MINIMO                                      ',0,1,null],
					[1415,'Repago de haberes                                 ',1,1,null],
					[1651,'PAD (Prestacion Anticipada por Desempleo)         ',0,1,null],
					[2461,'Suplemento docente                                ',1,1,null],
					[2607,'Moratoria                                         ',1,1,null],
					[2810,'Jubilación Docente                                ',1,1,null],
					[4127,'Informacion Sumaria Judicial                      ',1,1,null],
					[4510,'Jubilación Ordinaria (Ley 26222)                  ',0,1,null],
					[7224,'Acreditacion de convivencia                       ',0,1,'ACRED_CONV'],
					[7366,'Reajuste por incorporacion de servicios           ',1,1,null],
					[7383,'Minusvalia                                        ',1,1,null],
					[8106,'REPARACION HISTORICA                              ',1,1,null],
					[8124,'REPARACION HISTORICA- JUDICIAL                    ',1,1,null],
					[8125,'PUAM',1,1,null]
				];

        foreach ($items as $item) {
        	TipoTramite::create([
        		'id' => $item[0],
        		'nombre' => trim($item[1]),
        		'vigente' => $item[2],
        		'area_id' => $item[3],
        		'tratamiento' => $item[4]
        	]);
        }
    }
}
