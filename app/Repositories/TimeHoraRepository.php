<?php

namespace App\Repositories;

use App\Models\TimeHora;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class TimeHoraRepository
 * @package App\Repositories
 * @version November 29, 2018, 5:10 pm -03
 *
 * @method TimeHora findWithoutFail($id, $columns = ['*'])
 * @method TimeHora find($id, $columns = ['*'])
 * @method TimeHora first($columns = ['*'])
*/
class TimeHoraRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'cliente_id'        
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return TimeHora::class;
    }

    public function impresionesClienteAbogado($filtros,$offset=50)
    {
        $desde = \Carbon\Carbon::createFromFormat('d/m/Y',$filtros->get('desde'))->format('Y-m-d');
        $hasta = \Carbon\Carbon::createFromFormat('d/m/Y',$filtros->get('hasta'))->format('Y-m-d');
        $cliente_id = $filtros->get('cliente_id',false);

        $query = (new TimeHora())->newQuery()->with([
            'cliente' => function($q) {
                $q->select('nombre_completo','id');
            },
            'usuario' => function($q) {
                $q->select('name','username','id');
            },  
            'gestion' => function($q) {
                $q->select('nombre','id');
            },      
        ]);

        $query = $query->where('fecha','>=',$desde)->where('fecha','<=',$hasta)
                 ->whereClienteId($cliente_id)
                 ->orderBy('referencia')->orderBy('user_id')->orderBy('fecha')->orderBy('id');




        $resultados = $offset ? $query->paginate($offset)->toArray() : ['data' => $query->get()];
	//\Log::info($resultados);
        $data = [];
        $ref_actual = "11111111111";
        $minutos = 0;
        if (count($resultados['data']) > 0) 
        {
            
            foreach ($resultados['data'] as $row) 
            {
                //\Log::info($row);
                if ($row['referencia'] != $ref_actual)
                {
                    $ref_actual = $row['referencia'];
                    
                    if (count($data) > 0)
                    {
                        $data[] = [
                            'tipo' => 'PIE',
                            'minutos' => $minutos,
                        ];
                    }

                    $data[] = [
                        'tipo' => 'CABECERA',
                        'referencia' => $row['referencia'],
                    ];

                    $minutos = 0;
                }
                $minutos+= (int)$row['minutos']; 
                $data[] = $row;
            }

            if (count($data) > 0)
            {
                $data[] = [
                    'tipo' => 'PIE',
                    'minutos' => $minutos,
                ];
            }            
        }
        $data[] = [
            'tipo' => 'TOTAL',
            'minutos' => $query->sum('minutos'),
            'movimientos' => $query->count(),
        ];        
        $resultados['data'] = $data;

        //\Log::info(json_encode($resultados, JSON_PRETTY_PRINT));
        return $resultados;


        //return ($offset ? $query->paginate($offset) : $query->get());
    }

    public function impresionesAbogadoCliente($filtros,$offset=50)
    {
        $desde = \Carbon\Carbon::createFromFormat('d/m/Y',$filtros->get('desde'))->format('Y-m-d');
        $hasta = \Carbon\Carbon::createFromFormat('d/m/Y',$filtros->get('hasta'))->format('Y-m-d');
        $user_id = $filtros->get('user_id',null);

        $query = (new TimeHora())->newQuery()->with([
            'cliente' => function($q) {
                $q->select('nombre_completo','id');
            },
            'usuario' => function($q) {
                $q->select('name','username','id');
            },  
            'gestion' => function($q) {
                $q->select('nombre','id');
            },      
        ]);

        if ($user_id) {
            $query = $query->whereUserId($user_id);
        }

        $query = $query
                ->select('time_horas.*')
                ->leftJoin('clientes', 'clientes.id', '=', 'time_horas.cliente_id')
                ->where('fecha','>=',$desde)
                ->where('fecha','<=',$hasta)
                ->orderByRaw('clientes.nombre_completo asc')
                ->orderBy('referencia')
                ->orderBy('fecha')
                ->orderBy('time_horas.id');




        $resultados = $offset ? $query->paginate($offset)->toArray() : ['data' => $query->get()];
        $data = [];
        $ref_actual = 0;
        $minutos = 0;
        if (count($resultados['data']) > 0) 
        {
            
            foreach ($resultados['data'] as $row) 
            {
                //\Log::info($row);
                if ($row['cliente_id'] != $ref_actual)
                {
                    $ref_actual = $row['cliente_id'];
                    
                    if (count($data) > 0)
                    {
                        $data[] = [
                            'tipo' => 'PIE',
                            'minutos' => $minutos,
                        ];
                    }

                    $data[] = [
                        'tipo' => 'CABECERA',
                        'referencia' => $row['cliente']['nombre_completo'],
                    ];

                    $minutos = 0;
                }
                $minutos+= (int)$row['minutos']; 
                $data[] = $row;
            }

            if (count($data) > 0)
            {
                $data[] = [
                    'tipo' => 'PIE',
                    'minutos' => $minutos,
                ];
            }            
        }
        $data[] = [
            'tipo' => 'TOTAL',
            'minutos' => $query->sum('minutos'),
            'movimientos' => $query->count(),
        ];        
        $resultados['data'] = $data;

        //\Log::info($resultados->items);
        return $resultados;


        //return ($offset ? $query->paginate($offset) : $query->get());
    }  

    public function impresionesAbogadoResumen($filtros,$offset=50)
    {
        $desde = \Carbon\Carbon::createFromFormat('d/m/Y',$filtros->get('desde'))->format('Y-m-d');
        $hasta = \Carbon\Carbon::createFromFormat('d/m/Y',$filtros->get('hasta'))->format('Y-m-d');
        $cliente_id = $filtros->get('cliente_id',false);
        $user_id = $filtros->get('user_id',false);

        $query = (new TimeHora())->newQuery()->with([
            'cliente' => function($q) {
                $q->select('nombre_completo','id');
            },
            'usuario' => function($q) {
                $q->select('name','username','id');
            },  
            'gestion' => function($q) {
                $q->select('nombre','id');
            },      
        ]);

        $query = $query->where('fecha','>=',$desde)->where('fecha','<=',$hasta)
                 ->whereClienteId($cliente_id)
                 ->where(function($q) use ($user_id) {
                    if ($user_id) {
                        $q->whereUserId($user_id);
                    }
                 })
                 ->orderBy('user_id')->orderBy('referencia')->orderBy('fecha')->orderBy('id');




        $resultados = $offset ? $query->paginate($offset)->toArray() : ['data' => $query->get()];
        $data = [];
        $ref_actual = 0;
        $minutos = 0;
        if (count($resultados['data']) > 0) 
        {
            
            foreach ($resultados['data'] as $row) 
            {
                //\Log::info($row);
                if ($row['user_id'] != $ref_actual)
                {
                    $ref_actual = $row['user_id'];
                    
                    if (count($data) > 0)
                    {
                        $data[] = [
                            'tipo' => 'PIE',
                            'minutos' => $minutos,
                        ];
                    }

                    $data[] = [
                        'tipo' => 'CABECERA',
                        'referencia' => $row['usuario']['name'],
                    ];

                    $minutos = 0;
                }
                $minutos+= (int)$row['minutos']; 
                $data[] = $row;
            }

            if (count($data) > 0)
            {
                $data[] = [
                    'tipo' => 'PIE',
                    'minutos' => $minutos,
                ];
            }            
        }
        $data[] = [
            'tipo' => 'TOTAL',
            'minutos' => $query->sum('minutos'),
            'movimientos' => $query->count(),
        ];        
        $resultados['data'] = $data;

        //\Log::info($resultados->items);
        return $resultados;


        //return ($offset ? $query->paginate($offset) : $query->get());
    }      
}
