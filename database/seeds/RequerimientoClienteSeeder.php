<?php

use App\Models\RequerimientoCliente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequerimientoClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	RequerimientoCliente::truncate();
    	DB::statement('TRUNCATE TABLE responsable_requerimientos');
    	
        $items = [
			['2769','2953','0','1758','43','2753','2219','esposa direc san vicente',NULL,NULL,NULL],
			['7747','2953','0','1758','1415','2064',NULL,'',NULL,NULL,NULL]
        ];

        foreach ($items as $item) {
        	$model = RequerimientoCliente::create([
                'id' => $item[0],
                'area_id' => 1,
        		'cliente_id' => $item[1],
        		'colega_id' => ($item[2] == '0' ? NULL : $item[2]),
        		//'user_id' => $item[3],
        		'tipo_tramite_id' => $item[4],
        		'estado_req_id' => $item[5],
        		'tramite_id' => $item[6],
        		'recomendado' => $item[7],
        		'fecha_turno' => $item[8],
        		'hora_turno' => $item[9],
        		'rep_origen_id' => $item[10],
        	]);
        	$model->responsables()->create([
        		'requerimiento_id' => $model->id,
        		'user_id' => $item[3],
        		'fecha_asignacion' => NULL
        	]);
        }
    }
}
