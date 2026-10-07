<?php

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClientesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
	public function limpiarCP($valor) 
	{

		switch ($valor) {
			case NULL:
			case '          ':
			case '-':
			case '--':
			case '---':
			case '----':
			case '------':
			case '-------':
			case '----------':
			case '.':
			case '..':
			case '...':
			case '_-':
			case '0':
			case '0000':
			case '00000':
			case 'COMPLETAR':
			case 'NNNN':
			case 'NO':
			case 'RAFA':
			case 'S/N':
			case 'X':
			case 'XX':
			case 'XXX':
			case 'xxxx':
				# code...
				return NULL;
				break;
							
			default:
				return trim($valor);
				break;
		}
	} 

	public function limpiarEstadoCivil($valor)
	{
		switch ($valor) {
			case '0':
				return 'SO';
				break;
			case '1':
				return 'CA';
				break;
			case '2':
				return 'VI';
				break;
			case '3':
				return 'CO';
				break;
			default:
				return NULL;
				break;
		}
	}

    public function run()
    {
        Cliente::truncate();
        //DB::statement('TRUNCATE TABLE model_has_roles');
  
        $items = [
            ['2953','ABAL EVANGELINA MARIA','2006-08-08','490','1822232','27-01822232-4','0','SIXTO FERNANDEZ 250','LOMAS DE ZAMORA','1832','6','9','ESPOSA DE MARTINEZ EMPRESA SAN VICENTE','4244-7104dcdc','1945-11-21','0',NULL,'32','106','5974','1','','0','2006-08-10','2953','0',NULL,'                                                  ','                                                  ','0','          ','0','0',NULL,NULL,NULL,'destruida']
        ];

        /*
			0 => EntidadID
			1 => Nombre
			2 => FechaPrimEntrevista
			3 => TipoDocID
			4 => NumeroDoc
			5 => CUIT
			6 => Sexo
			7 => Direccion
			8 => Localidad
			9 => CodPostal
			10 => PaisID
			11 => ProvinciaID
			12 => Telefono1
			13 => Telefono2
			14 => FechaNacimiento
			15 => Nacionalidad
			16 => FechaIngPais
			17 => CategoriaID
			18 => TipoAporteID
			19 => EmpresaReferenciaID
			20 => Moratoria
			21 => email
			22 => AFJPID
			23 => FechaAlta
			24 => EmpresaID
			25 => EstadoCivil
			26 => Observaciones
			27 => ApellidoConyuge
			28 => NombreConyuge
			29 => TipoDocConyugeID
			30 => NumeroDocConyuge
			31 => AniosConvivencia
			32 => HijosComun
			33 => FechaAviso
			34 => FechaCasamiento
			35 => FechaEnviudez
			36 => UbicacionCarpeta
        */

        foreach ($items as $item) {
            $limpio = [
			  'id' => $item[0],
			  'personeria' => 'H',
			  'fecha_entrevista' => $item[2],
			  'nombre_completo' => trim($item[1]),
			  'cuit' => trim($item[5]),
			  'sexo' => ($item[6] == 0 ? 'F' : 'M'),
			  'tipo_doc_id' => $item[3],
			  'nro_doc' => trim($item[4]),
			  'nacionalidad' => ($item[15] == 0 ? 'A' : 'E'),
			  'fecha_ing_pais' => ($item[15] == 1 ? $item[16] : NULL),
			  'fecha_nac' => $item[14],
			  'pais_id' => $item[10],
			  'cp' => $this->limpiarCP(trim($item[9])),
			  'localidad' => trim($item[8]),
			  'provincia_id' => ($item[11] > 0 ? $item[11] : 7),
			  'clp_extranjero' => NULL,
			  'direccion' => trim($item[7]),
			  'emails' => [],
			  'telefonos' => [],
			  'categoria' => ($item[17] == 34 || $item[17] == 35 ? 'E' : 'P') ,
			  'empresa_referencia_id' => ($item[17] == 34 || $item[17] == 35 ? $item[19] : 0),
			  'tipo_aporte_id' => $item[18],
			  'estado_civil' => $this->limpiarEstadoCivil($item[25]),
			  'ubicacion_carpeta' => trim($item[36]),
			  'nombre_conyuge' => trim($item[28]),
			  'apellido_conyuge' => trim($item[27]),
			  'tipo_doc_conyuge_id' => $item[29],
			  'nro_doc_conyuge' => trim($item[30]),
			  'email_conyuge' => NULL,
			  'telefono_conyuge' => NULL,
            ];




            Cliente::create($limpio);

        }

    }
}
