<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateUpdateAvisoClienteAPIRequest;
use App\Models\AvisoCliente;
use App\Models\UserArea;
use App\Repositories\AvisoClienteRepository;
use App\Repositories\Criteria\CustomDataTableCriteria;
use App\User;
use Illuminate\Http\Request;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class AvisoClienteController
 * @package App\Http\Controllers\API
 */

class AvisoClienteAPIController extends AppBaseController
{
    /** @var  AvisoClienteRepository */
    private $repository;

    public function __construct(AvisoClienteRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the AvisoCliente.
     * GET|HEAD /avisoClientes
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->repository->pushCriteria(new CustomDataTableCriteria($request));
        $this->repository->pushCriteria(new RequestCriteria($request));
        $collection = $this->repository->paginate($request->get('per_page',10));

        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }

    public function pendientes(Request $request)
    {
        $this->repository->pushCriteria(new RequestCriteria($request));
        $collection = $this->queryPendientes($request)->all();
        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }    

    public function cantPendientes(Request $request)
    {
        $pagination = $this->queryPendientes($request)->paginate(1)->toArray();

        return $this->sendResponse($pagination['total'], trans('api.success'));
    }  

    public function vencimientos(Request $request)
    {

        $user_id = $request->user()->id;

        $sqlArray = [];

        foreach ($request->user()->areas as $area) {
            switch ($area->area_id) {
                case 1:
                    //Previsional
                    $_sql = $this->sqlVtosAreaPrevisional($user_id,$area->responsable);
                    if (count($_sql) > 0) 
                    {
                        $sqlArray = array_merge($sqlArray,$_sql);
                    }
                    break;
                case 2:
                    //Previsional
                    $_sql = $this->sqlVtosAreaLaboral($user_id,$area->responsable);
                    if (count($_sql) > 0) 
                    {
                        $sqlArray = array_merge($sqlArray,$_sql);
                    }
                    break;
                case 3:
                    //Previsional
                    $_sql = $this->sqlVtosAreaCivil($user_id,$area->responsable);
                    if (count($_sql) > 0) 
                    {
                        $sqlArray = array_merge($sqlArray,$_sql);
                    }
                    break;
                case 4:
                    //Previsional
                    $_sql = $this->sqlVtosAreaComercial($user_id,$area->responsable);
                    if (count($_sql) > 0) 
                    {
                        $sqlArray = array_merge($sqlArray,$_sql);
                    }
                    break;
                case 5:
                    //Previsional
                    $_sql = $this->sqlVtosAreaSocietario($user_id,$area->responsable);
                    if (count($_sql) > 0) 
                    {
                        $sqlArray = array_merge($sqlArray,$_sql);
                    }
                    break;
            }
        };
        
        if (count($sqlArray) > 0)
        {
            $sql = "SELECT v.*,et.nombre estado_tramite,tt.nombre tipo_tramite,c.nombre_completo cliente FROM (";
            $sql.= implode(' UNION ALL ',$sqlArray);
            $sql.= " ) AS v
                LEFT JOIN clientes c ON v.cliente_id = c.id
                LEFT JOIN estado_tramites et ON v.estado_tramite_id = et.id
                LEFT JOIN tipo_tramites tt ON v.tipo_tramite_id = tt.id
                ORDER BY fecha_vto ASC, cliente_id ASC
            ";
            $data = \DB::select($sql);
        }
        else
        {
            $data = [];
        }

        return ['data' => $data];

    }

    private function sqlVtosAreaPrevisional($user_id,$responsable)
    {
        $fechaDesdeBenef = \Carbon\Carbon::today()->subDays(30)->format('Y-m-d') . ' 00:00:00';
        $fechaHastaBenef = \Carbon\Carbon::today()->addDays(60)->format('Y-m-d') . ' 23:59:59';
        //$responsablesIds = implode(',',($responsable ? UserArea::whereAreaId(1)->select('user_id')->pluck('user_id')->toArray() : [$user_id]));
        $responsablesIds = $user_id;
        $sql = [];

        //Area Previsional. Beneficios y Exp Judiciales
        $sql[] = "SELECT 'BENEFICIO' AS tipo,t.area_id,t.requerimiento_id,r.tramite_id,r.cliente_id,r.tipo_tramite_id,t.expediente,t.estado_tramite_id,tb.fecha_cobro fecha_vto,tb.detalle
            FROM tramite_beneficios tb 
            INNER JOIN tramite_clientes t ON t.id = tb.tramite_id
            INNER JOIN requerimiento_clientes r ON r.tramite_id = t.id
            WHERE t.area_id = 1
            AND tb.deleted_at IS NULL 
            AND t.deleted_at IS NULL
            AND r.deleted_at IS NULL
            AND (tb.fecha_cobro BETWEEN '{$fechaDesdeBenef}' AND '{$fechaHastaBenef}')
            AND EXISTS(SELECT DISTINCT 1 FROM responsable_requerimientos rr WHERE rr.requerimiento_id = r.id AND rr.user_id IN ({$responsablesIds}) AND rr.deleted_at IS NULL)
        ";

        return $sql;
    }

    private function sqlVtosAreaLaboral($user_id,$responsable)
    {
        $fechaDesdeBenef = \Carbon\Carbon::today()->subMonths(3)->format('Y-m-d') . ' 00:00:00';
        $fechaHastaBenef = \Carbon\Carbon::today()->addDays(60)->format('Y-m-d') . ' 23:59:59';

        $fechaDesdeSeclo = \Carbon\Carbon::today()->subDays(30)->format('Y-m-d') . ' 00:00:00';
        $fechaHastaSeclo = \Carbon\Carbon::today()->addDays(60)->format('Y-m-d') . ' 23:59:59';

        //$responsablesIds = implode(',',($responsable ? UserArea::whereAreaId(2)->select('user_id')->pluck('user_id')->toArray() : [$user_id]));
        $responsablesIds = $user_id;
        $sql = [];

        //Area Laboral. Acuerdo de seclo, cuotas de seclo, cuotas de conciliacion
        $sql[] = "SELECT 'VTO.<br>SECLO' AS tipo,t.area_id,t.requerimiento_id,r.tramite_id,r.cliente_id,r.tipo_tramite_id,t.expediente,t.estado_tramite_id,DATE_ADD(DATE_ADD(t.fecha_beneficio, INTERVAL 2 YEAR),INTERVAL 6 MONTH) fecha_vto,'' as detalle
            FROM tramite_clientes t
            INNER JOIN requerimiento_clientes r ON r.tramite_id = t.id
            WHERE t.area_id = 2
            AND t.tipo_resolucion = 0
            AND t.deleted_at IS NULL
            AND r.deleted_at IS NULL
            AND (DATE_ADD(DATE_ADD(t.fecha_beneficio, INTERVAL 2 YEAR),INTERVAL 6 MONTH) BETWEEN '{$fechaDesdeBenef}' AND '{$fechaHastaBenef}')
            AND EXISTS(SELECT DISTINCT 1 FROM responsable_requerimientos rr WHERE rr.requerimiento_id = r.id AND rr.user_id IN ({$responsablesIds}) AND rr.deleted_at IS NULL)
        ";
        $sql[] = "SELECT 'SECLO ACORDADO' AS tipo,t.area_id,t.requerimiento_id,r.tramite_id,r.cliente_id,r.tipo_tramite_id,t.expediente,t.estado_tramite_id,tb.fecha_cobro fecha_vto,tb.detalle
            FROM tramite_beneficios tb 
            INNER JOIN tramite_clientes t ON t.id = tb.tramite_id
            INNER JOIN requerimiento_clientes r ON r.tramite_id = t.id
            WHERE t.area_id = 2
            AND t.tipo_resolucion = 0
            AND tb.deleted_at IS NULL 
            AND t.deleted_at IS NULL
            AND r.deleted_at IS NULL
            AND (tb.fecha_cobro BETWEEN '{$fechaDesdeSeclo}' AND '{$fechaHastaSeclo}')
            AND EXISTS(SELECT DISTINCT 1 FROM responsable_requerimientos rr WHERE rr.requerimiento_id = r.id AND rr.user_id IN ({$responsablesIds}) AND rr.deleted_at IS NULL)
        "; 
        $sql[] = "SELECT 'CONCILIACION' AS tipo,t.area_id,t.requerimiento_id,r.tramite_id,r.cliente_id,r.tipo_tramite_id,t.expediente,t.estado_tramite_id,tb.fecha_cobro fecha_vto,tb.detalle
            FROM tramite_conciliacion tb 
            INNER JOIN tramite_clientes t ON t.id = tb.tramite_id
            INNER JOIN requerimiento_clientes r ON r.tramite_id = t.id
            WHERE t.area_id = 2
            AND t.tipo_resolucion = 1
            AND tb.deleted_at IS NULL 
            AND t.deleted_at IS NULL
            AND r.deleted_at IS NULL
            AND (tb.fecha_cobro BETWEEN '{$fechaDesdeSeclo}' AND '{$fechaHastaSeclo}')
            AND EXISTS(SELECT DISTINCT 1 FROM responsable_requerimientos rr WHERE rr.requerimiento_id = r.id AND rr.user_id IN ({$responsablesIds}) AND rr.deleted_at IS NULL)
        ";                  

        return $sql;   

    }

    private function sqlVtosAreaCivil($user_id,$responsable)
    {
        $fechaDesdeBenef = \Carbon\Carbon::today()->subDays(30)->format('Y-m-d') . ' 00:00:00';
        $fechaHastaBenef = \Carbon\Carbon::today()->addDays(60)->format('Y-m-d') . ' 23:59:59';
        //$responsablesIds = implode(',',($responsable ? UserArea::whereAreaId(3)->select('user_id')->pluck('user_id')->toArray() : [$user_id]));
        $responsablesIds = $user_id;
        $sql = [];

        //Area Previsional. Beneficios y Exp Judiciales
        $sql[] = "SELECT 'MEDIACION' AS tipo,t.area_id,t.requerimiento_id,r.tramite_id,r.cliente_id,r.tipo_tramite_id,t.expediente,t.estado_tramite_id,tb.fecha_cobro fecha_vto,tb.detalle
            FROM tramite_beneficios tb 
            INNER JOIN tramite_clientes t ON t.id = tb.tramite_id
            INNER JOIN requerimiento_clientes r ON r.tramite_id = t.id
            WHERE t.area_id = 3
            AND tb.deleted_at IS NULL 
            AND t.deleted_at IS NULL
            AND r.deleted_at IS NULL
            AND (tb.fecha_cobro BETWEEN '{$fechaDesdeBenef}' AND '{$fechaHastaBenef}')
            AND EXISTS(SELECT DISTINCT 1 FROM responsable_requerimientos rr WHERE rr.requerimiento_id = r.id AND rr.user_id IN ({$responsablesIds}))
        ";

        return $sql;        
    }

    private function sqlVtosAreaComercial($user_id,$responsable)
    {
        $fechaDesdeBenef = \Carbon\Carbon::today()->subDays(30)->format('Y-m-d') . ' 00:00:00';
        $fechaHastaBenef = \Carbon\Carbon::today()->addDays(60)->format('Y-m-d') . ' 23:59:59';
        //$responsablesIds = implode(',',($responsable ? UserArea::whereAreaId(4)->select('user_id')->pluck('user_id')->toArray() : [$user_id]));
        $responsablesIds = $user_id;
        $sql = [];

        //Area Previsional. Beneficios y Exp Judiciales
        $sql[] = "SELECT 'MEDIACION' AS tipo,t.area_id,t.requerimiento_id,r.tramite_id,r.cliente_id,r.tipo_tramite_id,t.expediente,t.estado_tramite_id,tb.fecha_cobro fecha_vto,tb.detalle
            FROM tramite_beneficios tb 
            INNER JOIN tramite_clientes t ON t.id = tb.tramite_id
            INNER JOIN requerimiento_clientes r ON r.tramite_id = t.id
            WHERE t.area_id = 4
            AND tb.deleted_at IS NULL 
            AND t.deleted_at IS NULL
            AND r.deleted_at IS NULL
            AND (tb.fecha_cobro BETWEEN '{$fechaDesdeBenef}' AND '{$fechaHastaBenef}')
            AND EXISTS(SELECT DISTINCT 1 FROM responsable_requerimientos rr WHERE rr.requerimiento_id = r.id AND rr.user_id IN ({$responsablesIds}) AND rr.deleted_at IS NULL)
        ";

        return $sql;   
    }

    private function sqlVtosAreaSocietario($user_id,$responsable)
    {
        $fechaDesdeBenef = \Carbon\Carbon::today()->subDays(30)->format('Y-m-d') . ' 00:00:00';
        $fechaHastaBenef = \Carbon\Carbon::today()->addDays(60)->format('Y-m-d') . ' 23:59:59';
        //$responsablesIds = implode(',',($responsable ? UserArea::whereAreaId(5)->select('user_id')->pluck('user_id')->toArray() : [$user_id]));
        $responsablesIds = $user_id;
        $sql = [];

        //Area Previsional. Beneficios y Exp Judiciales
        $sql[] = "SELECT 'VTO.<br>DIRECTORIO' AS tipo,t.area_id,t.requerimiento_id,r.tramite_id,r.cliente_id,r.tipo_tramite_id,t.expediente,t.estado_tramite_id,t.fecha_vto,'' as detalle
            FROM tramite_clientes t
            INNER JOIN requerimiento_clientes r ON r.tramite_id = t.id
            WHERE t.area_id = 5
            AND r.tipo_tramite_id = 8126
            AND t.estado_tramite_id = 5143
            AND t.deleted_at IS NULL
            AND r.deleted_at IS NULL
            AND (t.fecha_vto BETWEEN '{$fechaDesdeBenef}' AND '{$fechaHastaBenef}')
            AND EXISTS(SELECT DISTINCT 1 FROM responsable_requerimientos rr WHERE rr.requerimiento_id = r.id AND rr.user_id IN ({$responsablesIds}) AND rr.deleted_at IS NULL)
        ";
        return $sql;

    }

    protected function queryPendientes(Request $request)
    {
        

        $request->user()->load('areas');

        $user_id = $request->user()->id;
        $area_ids = $request->user()->areas->pluck('area_id');

        $areas = $request->user()->areas->pluck('responsable','area_id');
        $resp_de_areas = [];
        $resp_de_usuarios = [];

        foreach ($areas as $a_id => $resp)
        {
            if ($resp)
            {
                $resp_de_areas[] = $a_id;
            }
        }

        $query = $this->repository->with([
            'user' => function($q) {
                $q->select('id','name');
            },
            'cliente'
        ])->scopeQuery(function($query) use($user_id,$area_ids,$resp_de_areas,$resp_de_usuarios) {
            $desde = \Carbon\Carbon::today()->subDays(config('themis.avisos.dias_vencidos',0))->format('Y-m-d 00:00:00');
            $hasta = \Carbon\Carbon::today()->addDays(config('themis.avisos.dias_previos',0))->format('Y-m-d 23:59:59');
            return $query
                        ->whereDescartado(false)
                        ->where(function ($query) use($user_id,$area_ids,$resp_de_areas,$resp_de_usuarios) {
                            $query->where(function ($query) use($user_id) {
                                $query->whereType('U')->whereTypeId($user_id);
                            })->orWhere(function ($query) use($area_ids){
                                $query->whereType('A')->whereIn('type_id',$area_ids);
                            })->orWhere(function ($query) use($user_id){
                                $query->whereUserId($user_id);
                            });

                            if (count($resp_de_areas) > 0)
                            {
                                $query->orWhere(function ($query) use($resp_de_areas){
                                    $query->whereType('A')->whereIn('type_id',$resp_de_areas);
                                })/*->orWhere(function ($query) use($resp_de_usuarios){
                                    $query->whereType('U')->whereIn('type_id',$resp_de_usuarios);
                                })*/;
                            }                            
                        })
                        ->where('fecha','<=',$hasta)->where('fecha','>=',$desde);
                        //->orderBy('fecha','asc');
        });

        return $query;
    }

    /**
     * Store a newly created AvisoCliente in storage.
     * POST /avisoClientes
     *
     * @param CreateAvisoClienteAPIRequest $request
     *
     * @return Response
     */

    public function store(CreateUpdateAvisoClienteAPIRequest $request)
    {
        $input = $request->all();

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified AvisoCliente.
     * GET|HEAD /avisoClientes/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    public function posponer($id, Request $request)
    {
        $input = $request->only('fecha');

        /** @var AvisoCliente $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Update the specified AvisoCliente in storage.
     * PUT/PATCH /avisoClientes/{id}
     *
     * @param  int $id
     * @param UpdateAvisoClienteAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateAvisoClienteAPIRequest $request)
    {
        $input = $request->all();

        /** @var AvisoCliente $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    public function descartar($id)
    {
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }
        $model->descartado = true;
        $model->save();
        

        return $this->sendResponse($model, trans('api.success'));
    }

    /**
     * Remove the specified AvisoCliente from storage.
     * DELETE /avisoClientes/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var AvisoCliente $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model->delete();

        return $this->sendResponse($id, trans('api.success'));
    }

    public function removeSelected(Request $request)
    {
        $ids = $request->get('ids',[]);
        $this->repository->deleteByIds($ids);
        return $this->sendResponse($ids, trans('api.success'));   
    }    
}
