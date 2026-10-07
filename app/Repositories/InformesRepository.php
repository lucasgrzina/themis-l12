<?php

namespace App\Repositories;

//use App\Models\Informes;
use App\Models\TramiteBeneficio;
use App\Models\TramiteExpediente;
use App\Models\TramiteCliente;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class InformesRepository
 * @package App\Repositories
 * @version May 9, 2018, 3:28 am UTC
 *
 * @method Informes findWithoutFail($id, $columns = ['*'])
 * @method Informes find($id, $columns = ['*'])
 * @method Informes first($columns = ['*'])
*/
class InformesRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return TramiteExpediente::class;
    }

    public function expedientesJudiciales($filtros,$offset=false) {
        $desde = \Carbon\Carbon::createFromFormat('d/m/Y',$filtros->get('desde'))->format('Y-m-d');
        $hasta = \Carbon\Carbon::createFromFormat('d/m/Y',$filtros->get('hasta'))->format('Y-m-d');
        $area_id = $filtros->get('area_id',1);
        $userId = $filtros->get('user_id');
        $responsableArea = filter_var($filtros->get('responsable_area',false),FILTER_VALIDATE_BOOLEAN);
			



        $query = (new TramiteExpediente())->newQuery()->with([
            'tramite' => function($q) {
                $q->select('id','cliente_id','area_id');
            },
            'tramite.cliente' => function($q) {
                $q->select('id','personeria','nombre_completo');
            },   
            'juzgado' => function($q) {
                $q->select('id','nombre');
            },   
            'area' => function($q) {
                $q->select('id','nombre');
            }          
        ]);
        
        if ($responsableArea) {
            $responsableId = $filtros->get('responsable_id',0);
            if ($responsableId != 0) {
                $query = $query->whereHas('tramite.requerimiento.responsables', function ($query) use ($responsableId){
                    $query->where('user_id',$responsableId);
                });
            }
        } else {
                $query = $query->whereHas('tramite.requerimiento.responsables', function ($query) use ($userId){
                    $query->where('user_id',$userId);
                });            
        }

        $query = $query->where('fecha','>=',$desde)->where('fecha','<=',$hasta)
                 ->whereAreaId($filtros->get('area_id'))
                 ->orderBy('fecha');


        return ($offset ? $query->paginate($offset) : $query->get());

    }

    public function beneficios($filtros,$offset=false) {
        $desde = \Carbon\Carbon::createFromFormat('d/m/Y',$filtros->get('desde'))->format('Y-m-d');
        $hasta = \Carbon\Carbon::createFromFormat('d/m/Y',$filtros->get('hasta'))->format('Y-m-d');
        $area_id = $filtros->get('area_id',1);
        $userId = $filtros->get('user_id');
        $responsableArea = filter_var($filtros->get('responsable_area',false),FILTER_VALIDATE_BOOLEAN);


        $query = \DB::table('tramite_clientes as t');

        $select = [];

        switch ($area_id) {
            case 1:
                $select = [
                        't.id',
                        't.fecha_beneficio',
                        'b.detalle',
                        't.nro_beneficio',
                        'b.detalle',
                        'b.fecha_cobro',
                        't.nro_beneficio',
                        'co.nombre as colega',
                        'ro.nombre as rep_origen',
                        'c.nombre_completo as nombre_cliente',
                        'c.id as id_cliente',
                        'c.cuit',
                        'c.categoria',
                        'c.empresa_referencia_id',
                        'e.razon_social as empresa',
                        'resp.responsables'                        
                ];
                break;
            case 2:
            case 3:
            case 4:
                $select = [
                        't.id',
                        't.fecha_beneficio',
                        'b.detalle',
                        't.nro_beneficio',
                        'b.detalle',
                        'b.fecha_cobro',
                        't.nro_beneficio',
                        'co.nombre as colega',
                        'r.autos',
                        'r.parte',
                        'c.nombre_completo as nombre_cliente',
                        'c.id as id_cliente',
                        'c.cuit',
                        'c.categoria',
                        'c.empresa_referencia_id',
                        'e.razon_social as empresa',                        
                        'resp.responsables'                        
                ];
                break;            
            
            case 5:
                break;
        }

        $query
            ->select($select)
            ->join('tramite_beneficios as b','b.tramite_id','=','t.id')
            ->join('requerimiento_clientes as r','r.id','=','t.requerimiento_id')
            ->leftJoin('clientes as c','c.id','=','t.cliente_id')
            ->leftJoin('empresas_referencia as e','e.id','=','c.empresa_referencia_id');

        if ($area_id == 1) {
            $query->leftJoin('reparticion_origen as ro','ro.id','=','t.rep_origen_id');
        }

        $query->leftJoin('estado_tramites as et','et.id','=','t.estado_tramite_id')
        ->leftJoin('tipo_tramites as tt','tt.id','=','r.tipo_tramite_id')
        ->leftJoin('colegas as co','co.id','=','r.colega_id')
        ->leftJoin(\DB::raw("(SELECT requerimiento_id, GROUP_CONCAT(u.name) responsables
                            FROM responsable_requerimientos rr 
                            INNER JOIN users u ON rr.user_id = u.id AND u.deleted_at IS NULL
                            WHERE rr.deleted_at is null
                            GROUP BY requerimiento_id) resp"), 
        function($join)
        {
           $join->on('resp.requerimiento_id', '=', 't.requerimiento_id');
        })      
        ->whereNull('c.deleted_at')              
        ->whereNull('t.deleted_at')
        ->whereNull('b.deleted_at')
        ->whereNull('r.deleted_at')
        ->where('t.area_id',$area_id)
        ->where('t.fecha_beneficio','>=',$desde)->where('fecha_beneficio','<=',$hasta)
        ->where('t.resolucion',1)
        ->where('t.tipo_resolucion',0)
        ->orderBy('t.fecha_beneficio')
        ->orderBy('t.cliente_id')
        ->orderBy('b.fecha_cobro')
        ->orderBy('b.id')
        ;

        if (!$responsableArea) {
            $responsableId = $userId;   
            $query->whereRaw("EXISTS(SELECT 1 FROM responsable_requerimientos WHERE deleted_at is null and requerimiento_id = t.requerimiento_id AND user_id IN (".$responsableId.") LIMIT 0,1) ");                                                       
        } else {
            $responsableId = $filtros->get('responsable_id',0);  
            if ($responsableId != 0) {
                $query->whereRaw("EXISTS(SELECT 1 FROM responsable_requerimientos WHERE deleted_at is null and requerimiento_id = t.requerimiento_id AND user_id IN (".$responsableId.") LIMIT 0,1) ");                              
            }  
        }

        if ($filtros->get('rep_origen_id',NULL) !== NULL) {
            $query->where('t.rep_origen_id',$filtros->get('rep_origen_id'));
        }
        if ($filtros->get('tipo_tramite_id',NULL) !== NULL) {
            $query->where('r.tipo_tramite_id',$filtros->get('tipo_tramite_id'));
        }
        if ($filtros->get('estado_tramite_id',NULL) !== NULL) {
            $query->where('t.estado_tramite_id',$filtros->get('estado_tramite_id'));
        }                                        





        return ($offset ? $query->paginate($offset) : $query->get());
    }   

    public function tramites($filtros,$offset=false) {
        //$desde = \Carbon\Carbon::createFromFormat('d/m/Y',$filtros->get('desde'))->format('Y-m-d');
        //$hasta = \Carbon\Carbon::createFromFormat('d/m/Y',$filtros->get('hasta'))->format('Y-m-d');
        
        $desde = $filtros->get('desde','');
        $hasta = $filtros->get('hasta','');


        $area_id = $filtros->get('area_id',1);
        $userId = $filtros->get('user_id');
        $responsableArea = filter_var($filtros->get('responsable_area',false),FILTER_VALIDATE_BOOLEAN);


        $query = \DB::table('tramite_clientes as t');
        $select = [];
        switch ($area_id) {
            case 1:
                $select = [
                    'resp.responsables',                        
                    'c.nombre_completo as nombre_cliente',
                    'c.clave_seguridad_social',
                    't.id as tramite_id',
                    't.expediente',
                    't.fecha_beneficio',
                    't.archivar',
                    'tt.nombre as tipo_tramite',
                    'r.tramite_id as req_tramite_id',
                    'ro.nombre as rep_origen',
                    'et.nombre as estado_tramite',
                    't.tipo_resolucion',
                    't.fecha_inicio as tramite_f_inicio',
                    'c.fecha_entrevista as fecha_alta',
                    'c.nro_doc as documento',
                    'c.id as id_cliente',
                    'c.cuit',
                    'c.categoria'                      
                ];
                break;
            case 2:
            case 3:
            case 4:
            case 5:
                $select = [
                    'resp.responsables',                        
                    'c.nombre_completo as nombre_cliente',
                    't.id as tramite_id',
                    't.expediente',
                    't.fecha_beneficio',
                    't.archivar',
                    'tt.nombre as tipo_tramite',
                    'r.tramite_id as req_tramite_id',
                    'r.autos',
                    'r.parte',
                    'et.nombre as estado_tramite',
                    't.tipo_resolucion',
                    't.fecha_inicio as tramite_f_inicio',
                    'c.fecha_entrevista as fecha_alta',
                    'c.nro_doc as documento',
                    'c.id as id_cliente',
                    'c.cuit',
                    'c.categoria'                      
                ];
                break;

        }

        $query
            ->select($select)
            ->join('requerimiento_clientes as r','r.id','=','t.requerimiento_id')
            ->leftJoin('clientes as c','c.id','=','t.cliente_id');
        
        if ($area_id == 1) {
            $query->leftJoin('reparticion_origen as ro','ro.id','=','t.rep_origen_id');
        }
        
        $query            
            ->leftJoin('estado_tramites as et','et.id','=','t.estado_tramite_id')
            ->leftJoin('tipo_tramites as tt','tt.id','=','r.tipo_tramite_id')
            ->leftJoin(\DB::raw("(SELECT requerimiento_id, GROUP_CONCAT(u.name) responsables
                                FROM responsable_requerimientos rr 
                                INNER JOIN users u ON rr.user_id = u.id AND u.deleted_at IS NULL
                                WHERE rr.deleted_at is null
                                GROUP BY requerimiento_id) resp"), 
            function($join)
            {
               $join->on('resp.requerimiento_id', '=', 't.requerimiento_id');
            }) 
            ->whereNull('c.deleted_at')                   
            ->whereNull('t.deleted_at')
            ->whereNull('r.deleted_at')
            ->where('t.area_id',$area_id);

            if ($filtros->get('estado_tramite_id',NULL) == 516)
            {
                //Beneficio acordado
                if ($desde != '' && $hasta != '') 
                {
                    $query->where('t.fecha_beneficio','>=',\Carbon\Carbon::createFromFormat('d/m/Y',$desde)->format('Y-m-d'))->where('t.fecha_beneficio','<=',\Carbon\Carbon::createFromFormat('d/m/Y',$hasta)->format('Y-m-d'));
                } 
                else if ($desde != '') 
                {
                    $query->where('t.fecha_beneficio','>=',\Carbon\Carbon::createFromFormat('d/m/Y',$desde)->format('Y-m-d'));
                } 
                else if ($hasta != '') 
                {
                    $query->where('t.fecha_beneficio','<=',\Carbon\Carbon::createFromFormat('d/m/Y',$hasta)->format('Y-m-d'));
                }            
                    
                $query->orderBy('t.fecha_beneficio')->orderBy('c.nombre_completo');
            }
            else
            {
                if ($desde != '' && $hasta != '') 
                {
                    $query->where('t.fecha_inicio','>=',\Carbon\Carbon::createFromFormat('d/m/Y',$desde)->format('Y-m-d'))->where('t.fecha_inicio','<=',\Carbon\Carbon::createFromFormat('d/m/Y',$hasta)->format('Y-m-d'));
                } 
                else if ($desde != '') 
                {
                    $query->where('t.fecha_inicio','>=',\Carbon\Carbon::createFromFormat('d/m/Y',$desde)->format('Y-m-d'));
                } 
                else if ($hasta != '') 
                {
                    $query->where('t.fecha_inicio','<=',\Carbon\Carbon::createFromFormat('d/m/Y',$hasta)->format('Y-m-d'));
                }            
                    
                $query->orderBy('t.fecha_inicio')->orderBy('c.nombre_completo');                
            }
            
            $query->where('t.archivar',0);

            if (!$responsableArea) {
                $responsableId = $userId;   
                $query->whereRaw("EXISTS(SELECT 1 FROM responsable_requerimientos WHERE deleted_at is null and requerimiento_id = t.requerimiento_id AND user_id IN (".$responsableId.") LIMIT 0,1) ");                                                       
            } else {
                $responsableId = $filtros->get('responsable_id',0);  
                if ($responsableId != 0) {
                    $query->whereRaw("EXISTS(SELECT 1 FROM responsable_requerimientos WHERE deleted_at is null and requerimiento_id = t.requerimiento_id AND user_id IN (".$responsableId.") LIMIT 0,1) ");                              
                }  
            }

            if ($filtros->get('rep_origen_id',NULL) !== NULL) {
                $query->where('t.rep_origen_id',$filtros->get('rep_origen_id'));
            }
            if ($filtros->get('tipo_tramite_id',NULL) !== NULL) {
                $query->where('r.tipo_tramite_id',$filtros->get('tipo_tramite_id'));
            }
            if ($filtros->get('estado_tramite_id',NULL) !== NULL) {
                $query->where('t.estado_tramite_id',$filtros->get('estado_tramite_id'));
            }                                        

        return ($offset ? $query->paginate($offset) : $query->get());
    }      

    public function pensiones($filtros,$offset=50) {

        $query = \DB::table('requerimiento_clientes as r')
        ->select(
            'c.nombre_completo as cliente',
            'c.tipo_doc_id',
            'c.nro_doc',
            'td.nombre as tipo_doc',
            'r.nombre_causante',
            'r.tipo_doc_id_causante',
            'r.nro_doc_causante as nro_doc_causante',
            'tdc.nombre as tipo_doc_causante'
        )
        ->join('tramite_clientes as t','t.requerimiento_id','=','r.id')
        ->join('clientes as c','c.id','=','t.cliente_id')
        ->join('tipo_tramites as tt','tt.id','=','r.tipo_tramite_id')
        ->join('tipo_documentos as td','c.tipo_doc_id','=','td.id')
        ->join('tipo_documentos as tdc','.tipo_doc_id_causante','=','tdc.id')
        ->whereNull('c.deleted_at')
        ->whereNull('t.deleted_at')
        ->whereNull('r.deleted_at')
        ->where('tt.tratamiento','PENSION')
        ->where('t.archivar',0)
        ->orderBy('c.nombre_completo');


        return $query->paginate($offset);
    }     

    public function tramitesHistoricos($filtros,$offset=false) {
        //$desde = \Carbon\Carbon::createFromFormat('d/m/Y',$filtros->get('desde'))->format('Y-m-d');
        //$hasta = \Carbon\Carbon::createFromFormat('d/m/Y',$filtros->get('hasta'))->format('Y-m-d');
        $desde = $filtros->get('desde','');
        $hasta = $filtros->get('hasta','');
        
        $area_id = $filtros->get('area_id',1);
        $userId = $filtros->get('user_id');
        $responsableArea = filter_var($filtros->get('responsable_area',false),FILTER_VALIDATE_BOOLEAN);


        $query = \DB::table('tramite_clientes as t');

        $select = [];
        switch ($area_id) {
            case 1:
                $select = [
                        'resp.responsables',                        
                        'c.nombre_completo as nombre_cliente',
                        't.id as tramite_id',
                        't.expediente',
                        't.fecha_beneficio',
                        't.archivar',
                        'tt.nombre as tipo_tramite',
                        'r.tramite_id as req_tramite_id',
                        't.tipo_resolucion',
                        't.fecha_inicio as tramite_f_inicio',
                        'c.fecha_entrevista as fecha_alta',
                        'c.nro_doc as documento',
                        'c.id as id_cliente',
                        'c.cuit',
                        'c.categoria',
                        'e.razon_social as empresas_referencia',
                        't.fecha_archivo as fecha_archivo',
                        'u.name as usuario_archivo'
                ];
                break;
            case 2:
            case 3:
            case 4:
            case 5:
                $select = [
                        'resp.responsables',                        
                        'c.nombre_completo as nombre_cliente',
                        't.id as tramite_id',
                        't.expediente',
                        't.fecha_beneficio',
                        't.archivar',
                        'tt.nombre as tipo_tramite',
                        'r.tramite_id as req_tramite_id',
                        'r.autos',
                        'r.parte',
                        't.tipo_resolucion',
                        't.fecha_inicio as tramite_f_inicio',
                        'c.fecha_entrevista as fecha_alta',
                        'c.nro_doc as documento',
                        'c.id as id_cliente',
                        'c.cuit',
                        'c.categoria',
                        'e.razon_social as empresas_referencia',
                        't.fecha_archivo as fecha_archivo',
                        'u.name as usuario_archivo'
                ];
                break;
        }

        $query
            ->select($select)
            ->join('requerimiento_clientes as r','r.id','=','t.requerimiento_id')
            ->leftJoin('clientes as c','c.id','=','t.cliente_id')
            ->leftJoin('tipo_tramites as tt','tt.id','=','r.tipo_tramite_id')
            ->leftJoin('empresas_referencia as e','e.id','=','c.empresa_referencia_id')
            ->leftJoin('users as u','t.usuario_archivo_id','=','u.id')
            ->leftJoin(\DB::raw("(SELECT requerimiento_id, GROUP_CONCAT(u.name) responsables
                                FROM responsable_requerimientos rr 
                                INNER JOIN users u ON rr.user_id = u.id AND u.deleted_at IS NULL
                                WHERE rr.deleted_at is null
                                GROUP BY requerimiento_id) resp"), 
            function($join)
            {
               $join->on('resp.requerimiento_id', '=', 't.requerimiento_id');
            }) 
            ->whereNull('c.deleted_at')                   
            ->whereNull('t.deleted_at')
            ->whereNull('r.deleted_at')
            ->whereNull('u.deleted_at')
            ->where('t.area_id',$area_id)
            //->where('t.fecha_inicio','>=',$desde)->where('fecha_inicio','<=',$hasta)
            ->where('t.archivar',1)
            ->orderBy('c.nombre_completo');

        if ($desde != '' && $hasta != '') {
            $query->where('t.fecha_inicio','>=',\Carbon\Carbon::createFromFormat('d/m/Y',$desde)->format('Y-m-d'))->where('t.fecha_inicio','<=',\Carbon\Carbon::createFromFormat('d/m/Y',$hasta)->format('Y-m-d'));
        } else if ($desde != '') {
            $query->where('t.fecha_inicio','>=',\Carbon\Carbon::createFromFormat('d/m/Y',$desde)->format('Y-m-d'));
        } else if ($hasta != '') {
            $query->where('t.fecha_inicio','<=',\Carbon\Carbon::createFromFormat('d/m/Y',$hasta)->format('Y-m-d'));
        }

        if (!$responsableArea) {
            $responsableId = $userId;   
            $query->whereRaw("EXISTS(SELECT 1 FROM responsable_requerimientos WHERE deleted_at is null and requerimiento_id = t.requerimiento_id AND user_id IN (".$responsableId.") LIMIT 0,1) ");                                                       
        } else {
            $responsableId = $filtros->get('responsable_id',0);  
            if ($responsableId != 0) {
                $query->whereRaw("EXISTS(SELECT 1 FROM responsable_requerimientos WHERE deleted_at is null and requerimiento_id = t.requerimiento_id AND user_id IN (".$responsableId.") LIMIT 0,1) ");                              
            }  
        }

        return ($offset ? $query->paginate($offset) : $query->get());
    }      

    public function requerimientosEmpresas($filtros,$offset=false) {
        $desde = $filtros->get('desde','');
        $hasta = $filtros->get('hasta','');
        $area_id = $filtros->get('area_id',1);
        $userId = $filtros->get('user_id');
        $responsableArea = filter_var($filtros->get('responsable_area',false),FILTER_VALIDATE_BOOLEAN);

        $query = \DB::table('requerimiento_clientes as r');

        $select = [];
        switch ($area_id) {
            case 1:
                $select = [
                    'resp.responsables',                        
                    'c.nombre_completo as nombre_cliente',
                    't.id as tramite_id',
                    't.expediente',
                    't.fecha_beneficio',
                    't.archivar',
                    'tt.nombre as tipo_tramite',
                    'r.tramite_id as req_tramite_id',
                    't.tipo_resolucion',
                    't.fecha_inicio as tramite_f_inicio',
                    'c.fecha_entrevista as fecha_alta',
                    'c.nro_doc as documento',
                    'c.id as id_cliente',
                    'c.cuit',
                    'c.categoria',
                    'e.razon_social as empresas_referencia',
                    't.fecha_archivo as fecha_archivo',
                    't.usuario_archivo_id as usuario_archivo',
                    'er.nombre as estado_requerimiento',
                    'et.nombre as estado_tramite',
                    'ro.nombre as rep_origen',
                    't.nro_beneficio'
                ];
                break;
            case 2:
            case 3:
            case 4:
                $select = [
                    'resp.responsables',                        
                    'c.nombre_completo as nombre_cliente',
                    't.id as tramite_id',
                    't.expediente',
                    't.fecha_beneficio',
                    't.archivar',
                    'tt.nombre as tipo_tramite',
                    'r.tramite_id as req_tramite_id',
                    't.tipo_resolucion',
                    't.fecha_inicio as tramite_f_inicio',
                    'c.fecha_entrevista as fecha_alta',
                    'c.nro_doc as documento',
                    'c.id as id_cliente',
                    'c.cuit',
                    'c.categoria',
                    'e.razon_social as empresas_referencia',
                    't.fecha_archivo as fecha_archivo',
                    't.usuario_archivo_id as usuario_archivo',
                    'er.nombre as estado_requerimiento',
                    'et.nombre as estado_tramite',
                    'r.autos',
                    'r.parte',
                    't.nro_beneficio'
                ]; 
            case 5:
                $select = [
                    'resp.responsables',                        
                    'c.nombre_completo as nombre_cliente',
                    't.id as tramite_id',
                    't.expediente',
                    't.fecha_beneficio',
                    't.archivar',
                    'tt.nombre as tipo_tramite',
                    'r.tramite_id as req_tramite_id',
                    't.tipo_resolucion',
                    't.fecha_inicio as tramite_f_inicio',
                    'c.fecha_entrevista as fecha_alta',
                    'c.nro_doc as documento',
                    'c.id as id_cliente',
                    'c.cuit',
                    'c.categoria',
                    'e.razon_social as empresas_referencia',
                    't.fecha_archivo as fecha_archivo',
                    't.usuario_archivo_id as usuario_archivo',
                    'er.nombre as estado_requerimiento',
                    'et.nombre as estado_tramite',
                    'r.autos',
                    'r.parte',
                    't.nro_beneficio'
                ];                                        
                break;
        }

        $query
            ->select($select)
            ->leftJoin('tramite_clientes as t','t.id','=','r.tramite_id')
            ->leftJoin('clientes as c','c.id','=','r.cliente_id')
            ->leftJoin('tipo_tramites as tt','tt.id','=','r.tipo_tramite_id')
            ->leftJoin('reparticion_origen as ro','ro.id','=','t.rep_origen_id')
            ->leftJoin('estado_requerimientos as er','er.id','=','r.estado_req_id')
            ->leftJoin('estado_tramites as et','et.id','=','t.estado_tramite_id')
            ->leftJoin('empresas_referencia as e','e.id','=','c.empresa_referencia_id')
            ->leftJoin(\DB::raw("(SELECT requerimiento_id, GROUP_CONCAT(u.name) responsables
                                FROM responsable_requerimientos rr 
                                INNER JOIN users u ON rr.user_id = u.id AND u.deleted_at IS NULL
                                WHERE rr.deleted_at is null
                                GROUP BY requerimiento_id) resp"), 
            function($join)
            {
               $join->on('resp.requerimiento_id', '=', 't.requerimiento_id');
            }) 
            ->whereNull('c.deleted_at')                   
            ->whereNull('t.deleted_at')
            ->whereNull('r.deleted_at')
            ->where('r.area_id',$area_id)
            //->where('t.archivar',0)
            ->orderBy('c.nombre_completo');

                    
            if (!$responsableArea) {
                $responsableId = $userId;   
                $query->whereRaw("EXISTS(SELECT 1 FROM responsable_requerimientos WHERE deleted_at is null and requerimiento_id = t.requerimiento_id AND user_id IN (".$responsableId.") LIMIT 0,1) ");                                                       
            } else {
                $responsableId = $filtros->get('responsable_id',0);  
                if ($responsableId != 0) {
                    $query->whereRaw("EXISTS(SELECT 1 FROM responsable_requerimientos WHERE deleted_at is null and requerimiento_id = r.id AND user_id IN (".$responsableId.") LIMIT 0,1) ");                              
                }  
            }

            if ($desde != '' && $hasta != '') {
                $desdeFormateado = \Carbon\Carbon::createFromFormat('d/m/Y',$desde)->format('Y-m-d');
                $hastaFormateado = \Carbon\Carbon::createFromFormat('d/m/Y',$hasta)->format('Y-m-d');
                $query->where(function($query) use ($desdeFormateado,$hastaFormateado) {
                    $query->where('r.created_at',null)
                        ->orWhere(function($query) use ($desdeFormateado,$hastaFormateado) {
                            $query->where('r.created_at','>=',$desdeFormateado)->where('r.created_at','<=',$hastaFormateado );
                        });
                });                
                
            } else if ($desde != '') {
                $desdeFormateado = \Carbon\Carbon::createFromFormat('d/m/Y',$desde)->format('Y-m-d');
                $query->where(function($query) use ($desdeFormateado) {
                    $query->where('r.created_at','>=',$desdeFormateado)
                        ->orWhere('r.created_at', null);
                });
            } else if ($hasta != '') {
                $hastaFormateado = \Carbon\Carbon::createFromFormat('d/m/Y',$hasta)->format('Y-m-d');
                $query->where(function($query) use ($hastaFormateado) {
                    $query->where('r.created_at','<=',$hastaFormateado)
                        ->orWhere('r.created_at', null);
                });
                //$query->where('r.created_at','<=',\Carbon\Carbon::createFromFormat('d/m/Y',$hasta)->format('Y-m-d'));
            }

            if ($filtros->get('codigo_desde',NULL) !== NULL) {
                $query->where('c.id','>=',$filtros->get('codigo_desde'));
            }
            if ($filtros->get('codigo_hasta',NULL) !== NULL) {
                $query->where('c.id','<=',$filtros->get('codigo_hasta'));
            }                    
            if ($filtros->get('nombre_desde',NULL) !== NULL) {
                $query->where('c.nombre_completo','>=',$filtros->get('nombre_desde'));
            }
            if ($filtros->get('nombre_hasta',NULL) !== NULL) {
                $query->where('c.nombre_completo','<=',$filtros->get('nombre_hasta'));
            }                    
            if ($filtros->get('cuit',NULL) !== NULL) {
                $query->where('c.cuit',$filtros->get('cuit'));
            }    

            if ($filtros->get('rep_origen_id',NULL) !== NULL) {
                $query->where('t.rep_origen_id',$filtros->get('rep_origen_id'));
            }
            if ($filtros->get('tipo_tramite_id',NULL) !== NULL) {
                $query->where('r.tipo_tramite_id',$filtros->get('tipo_tramite_id'));
            }
            if ($filtros->get('estado_req_id',NULL) !== NULL) {
                $query->where('r.estado_req_id',$filtros->get('estado_req_id'));
            } 
            if ($filtros->get('colega_id',NULL) !== NULL) {
                $query->where('r.colega_id',$filtros->get('colega_id'));
            } 
            if ($filtros->get('categoria',NULL) !== NULL) {
                $query->where('c.categoria',$filtros->get('categoria'));
            } 
            if ($filtros->get('empresa_referencia_id',NULL) !== NULL) {
                $query->where('c.empresa_referencia_id',$filtros->get('empresa_referencia_id'));
            }  


        $resultados = $offset ? $query->paginate($offset)->toArray() : ['data' => $query->get()];
        $data = [];
        $id_cliente_actual = 0;
        if (count($resultados['data']) > 0) 
        {
            foreach ($resultados['data'] as $row) 
            {
                if ($row->id_cliente != $id_cliente_actual)
                {
                    $id_cliente_actual = $row->id_cliente;
                    
                    $data[] = [
                        'tipo' => 'CABECERA',
                        'nombre_cliente' => $row->nombre_cliente,
                        'id_cliente' => $row->id_cliente,
                        'fecha_alta' => $row->fecha_alta 
                    ];
                }
                $data[] = $row;
            }
        }
        $resultados['data'] = $data;
        //\Log::info($resultados->items);
        return $resultados;
    }    

    public function ucadep($filtros,$offset=false) {
        $desde = $filtros->get('desde','');
        $hasta = $filtros->get('hasta','');
        $ansesDesde = $filtros->get('anses_desde','');
        $ansesHasta = $filtros->get('anses_hasta','');  
        $estadoAnses= $filtros->get('estado_anses_id',0);        
        $ultimoEstado= filter_var($filtros->get('ultimo_estado',false),FILTER_VALIDATE_BOOLEAN);        
        $area_id = $filtros->get('area_id',1);
        $userId = $filtros->get('user_id');
        $responsableArea = filter_var($filtros->get('responsable_area',false),FILTER_VALIDATE_BOOLEAN);
            

        $with = [
            'anses' => function($q) {
                $q->orderBy('fecha_remision','asc')->orderBy('id','asc');
            },
            'anses.estado' => function($q) {
                $q->select('id','nombre');
            }/*,     
            'ultimoEstadioAnses.estado' => function($q) {
                $q->select('id','nombre');
            }*/,
            'cliente' => function($q) {
                $q->select('id','personeria','nombre_completo','nombre_conyuge','apellido_conyuge','tipo_doc_conyuge_id','nro_doc_conyuge','cuit');
            },   
            'tramiteAnt'
        ];

        if ($estadoAnses != 0 && $ultimoEstado)
        {
            $with['ultimoEstadioAnses.estado'] = function($q) {
                $q->select('id','nombre');
            };

        }


        $query = (new TramiteCliente())->newQuery()->with($with);
        
        $query->whereHas('anses',function($query) use ($desde,$hasta,$estadoAnses,$ultimoEstado) {
            if ($desde != '' && $hasta != '') {
                $query->where('fecha_remision','>=',\Carbon\Carbon::createFromFormat('d/m/Y',$desde)->format('Y-m-d'))->where('fecha_remision','<=',\Carbon\Carbon::createFromFormat('d/m/Y',$hasta)->format('Y-m-d'));
            } else if ($desde != '') {
                $query->where('fecha_remision','>=',\Carbon\Carbon::createFromFormat('d/m/Y',$desde)->format('Y-m-d'));
            } else if ($hasta != '') {
                $query->where('fecha_remision','<=',\Carbon\Carbon::createFromFormat('d/m/Y',$hasta)->format('Y-m-d'));
            }

            if ($estadoAnses != 0)
            {
                $query->where('estado_anses_id',$estadoAnses);
                if ($ultimoEstado)
                {

                    $query->whereRaw('fecha_remision = (select max(`fecha_remision`) from tramite_anses WHERE tramite_anses.tramite_id = tramite_clientes.id)');
                }
                
            }
        })
        ->where('archivar',false)
        ->orderBy('fecha_remision_vto','asc');



        if ($ansesDesde != '' && $ansesHasta != '') {
            $query->where('fecha_inicio','>=',\Carbon\Carbon::createFromFormat('d/m/Y',$ansesDesde)->format('Y-m-d'))->where('fecha_inicio','<=',\Carbon\Carbon::createFromFormat('d/m/Y',$ansesHasta)->format('Y-m-d'));
        } else if ($ansesDesde != '') {
            $query->where('fecha_inicio','>=',\Carbon\Carbon::createFromFormat('d/m/Y',$ansesDesde)->format('Y-m-d'));
        } else if ($ansesHasta != '') {
            $query->where('fecha_inicio','<=',\Carbon\Carbon::createFromFormat('d/m/Y',$ansesHasta)->format('Y-m-d'));
        }


        if ($responsableArea) {
            $responsableId = $filtros->get('responsable_id',0);
            if ($responsableId != 0) {
                $query = $query->whereHas('requerimiento.responsables', function ($query) use ($responsableId){
                    $query->where('user_id',$responsableId);
                });
            }
        } else {
                $query = $query->whereHas('requerimiento.responsables', function ($query) use ($userId){
                    $query->where('user_id',$userId);
                });            
        }


        return ($offset ? $query->paginate($offset) : $query->get());

/*
        $query = \DB::table('tramite_anses as ta')
                ->select(
                    'c.id',
                    'c.nombre_completo',
                    'c.cuit',
                    'tm.expediente',
                    't.nro_beneficio',
                    'ta.estado_anses_id',
                    'e.nombre as estado',
                    'ta.fecha_remision'                
                )
                ->join('tramite_clientes as t','t.id','=','ta.tramite_id')
                ->leftJoin('tramite_clientes as tm','tm.id','=','t.tramite_ant_id')
                ->leftJoin('clientes as c','c.id','=','t.cliente_id')
                ->leftJoin('estado_anses as e','e.id','=','ta.estado_anses_id')
                ->whereNull('ta.deleted_at')                   
                ->whereNull('t.deleted_at')
                ->whereNull('tm.deleted_at')
                ->whereNull('c.deleted_at')
                ->whereNull('e.deleted_at')
                ->where('t.area_id',$area_id)
                ->orderBy('t.id')->orderBy('ta.fecha_remision');


                $desde = $filtros->get('desde','');//\Carbon\Carbon::createFromFormat('d/m/Y',$filtros->get('desde'))->format('Y-m-d');
                $hasta = $filtros->get('hasta','');//\Carbon\Carbon::createFromFormat('d/m/Y',$filtros->get('hasta'))->format('Y-m-d');

                if ($desde != '' && $hasta != '') {
                    $query->where('ta.fecha_remision','>=',\Carbon\Carbon::createFromFormat('d/m/Y',$desde)->format('Y-m-d'))->where('ta.fecha_remision','<=',\Carbon\Carbon::createFromFormat('d/m/Y',$hasta)->format('Y-m-d'));
                } else if ($desde != '') {
                    $query->where('ta.fecha_remision','>=',\Carbon\Carbon::createFromFormat('d/m/Y',$desde)->format('Y-m-d'));
                } else if ($hasta != '') {
                    $query->where('ta.fecha_remision','<=',\Carbon\Carbon::createFromFormat('d/m/Y',$hasta)->format('Y-m-d'));
                }

                if (!$responsableArea) {
                    $responsableId = $userId;   
                    $query->whereRaw("EXISTS(SELECT 1 FROM responsable_requerimientos WHERE deleted_at is null and requerimiento_id = t.requerimiento_id AND user_id IN (".$responsableId.") LIMIT 0,1) ");                                                       
                } else {
                    $responsableId = $filtros->get('responsable_id',0);  
                    if ($responsableId != 0) {
                        $query->whereRaw("EXISTS(SELECT 1 FROM responsable_requerimientos WHERE deleted_at is null and requerimiento_id = t.requerimiento_id AND user_id IN (".$responsableId.") LIMIT 0,1) ");                              
                    }  
                }
                /*
                if ($filtros->get('estado_tramite_id',NULL) !== NULL) {
                    $query->where('ta.estado_anses_id',$filtros->get('estado_tramite_id'));
                }                                        

        */

        return ($offset ? $query->paginate($offset) : $query->get());
    }           
}
