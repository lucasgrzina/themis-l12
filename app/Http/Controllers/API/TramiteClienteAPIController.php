<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Traits\TramitesTrait;
use App\Http\Requests\API\CreateUpdateTramiteClienteAPIRequest;
use App\Models\TramiteBeneficio;
use App\Models\TramiteCliente;
use App\Models\TramiteConciliacion;
use App\Repositories\Criteria\CustomDataTableCriteria;
use App\Repositories\TramiteBeneficioRepository;
use App\Repositories\TramiteClienteRepository;
use App\Repositories\TramiteConciliacionRepository;
use Illuminate\Http\Request;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class TramiteClienteController
 * @package App\Http\Controllers\API
 */

class TramiteClienteAPIController extends AppBaseController
{
    use TramitesTrait;
    /** @var  TramiteClienteRepository */
    private $repository;

    public function __construct(TramiteClienteRepository $repo)
    {
        $this->repository = $repo;
    }

    private function _cargarRelaciones($grupo='all')
    {
        $relaciones = [
            'all' => [
            ],
            'tramite' => [
                'repOrigen' => function($query) {
                    $query->select('id','nombre');
                },
                'estado' => function($query) {
                    $query->select('id','nombre');
                },                        
                'beneficios' => function($query) {
                    $query->select('id','tramite_id','detalle','fecha_cobro');
                },  
                'requerimiento' => function($query) {
                    $query->select('id','area_id','estado_req_id','tipo_tramite_id');
                },
                'requerimiento.estado' => function($query) {
                    $query->select('id','nombre');
                },
                'expJudicial.juzgado',
                'tramiteSig' => function($query) {
                    $query->select('id','estado_tramite_id','expediente','fecha_inicio','tramite_ant_id','tramite_sig_id');
                }
            ]
        ];

        return $relaciones[$grupo];    
        
    }    

    /**
     * Display a listing of the TramiteCliente.
     * GET|HEAD /tramiteClientes
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->repository->pushCriteria(new CustomDataTableCriteria($request));
        $this->repository->pushCriteria(new RequestCriteria($request));
        
        $collection = $this->repository->with($this->_cargarRelacionesTramites())->paginate($request->get('per_page',10));    
        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }

    /**
     * Store a newly created TramiteCliente in storage.
     * POST /tramiteClientes
     *
     * @param CreateTramiteClienteAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateTramiteClienteAPIRequest $request)
    {
        $input = $request->all();

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified TramiteCliente.
     * GET|HEAD /tramiteClientes/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $model = $this->repository->with($this->_cargarRelacionesTramites('tramite'))->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Update the specified TramiteCliente in storage.
     * PUT/PATCH /tramiteClientes/{id}
     *
     * @param  int $id
     * @param UpdateTramiteClienteAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateTramiteClienteAPIRequest $request)
    {
        $input = $request->all();

        /** @var TramiteCliente $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified TramiteCliente from storage.
     * DELETE /tramiteClientes/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var TramiteCliente $model */
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

    public function getBeneficios($clienteId,$tramiteId) 
    {
        $model = $this->repository->findWithoutFail($tramiteId);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model->load('beneficios');

        return $this->sendResponse($model->beneficios, trans('api.success'));

    }

    public function storeBeneficios($id,$tramiteId,Request $request, TramiteBeneficioRepository $beneRepo)
    {
        $model = new TramiteBeneficio();
        //Proceso el requerimiento.
        $except = ['id','created_at','updated_at','deleted_at'];
        
        $datos_basicos = $request->except($except);

        try
        {
            \DB::beginTransaction();

            //Guardo los datos del requerimiento
            $model->fill($datos_basicos);
            

            $model->save();

            \DB::commit();
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            return $this->sendError($e->getMessage());
        }
        
        //$model->save();
        return $this->sendResponse($model->toArray(), trans('api.success'));
    }    

    public function updateBeneficios($id,$tramiteId,$beneficioId,Request $request, TramiteBeneficioRepository $beneRepo)
    {
        $model = $beneRepo->findWithoutFail($beneficioId);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }
                //Proceso el requerimiento.
        $except = ['id','created_at','updated_at','deleted_at'];
        
        $datos_basicos = $request->except($except);

        try
        {
            \DB::beginTransaction();

            //Guardo los datos del requerimiento
            $model->fill($datos_basicos);
            

            $model->save();

            \DB::commit();
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            return $this->sendError($e->getMessage());
        }
        
        //$model->save();
        return $this->sendResponse($model->toArray(), trans('api.success'));
    }  

    public function destroyBeneficios($clienteId,$tramiteId,$beneficioId, TramiteBeneficioRepository $beneRepo)
    {
        $model = $beneRepo->findWithoutFail($beneficioId);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model->delete();

        return $this->sendResponse($beneficioId, trans('api.success'));

    }

    public function getConciliacion($clienteId,$tramiteId) 
    {
        $model = $this->repository->findWithoutFail($tramiteId);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model->load('conciliacion');

        return $this->sendResponse($model->conciliacion, trans('api.success'));

    }

    public function storeConciliacion($id,$tramiteId,Request $request, TramiteConciliacionRepository $beneRepo)
    {
        $model = new TramiteConciliacion();
        //Proceso el requerimiento.
        $except = ['id','created_at','updated_at','deleted_at'];
        
        $datos_basicos = $request->except($except);

        try
        {
            \DB::beginTransaction();

            //Guardo los datos del requerimiento
            $model->fill($datos_basicos);
            

            $model->save();

            \DB::commit();
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            return $this->sendError($e->getMessage());
        }
        
        //$model->save();
        return $this->sendResponse($model->toArray(), trans('api.success'));
    }    

    public function updateConciliacion($id,$tramiteId,$conciliacionId,Request $request, TramiteConciliacionRepository $beneRepo)
    {
        $model = $beneRepo->findWithoutFail($conciliacionId);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }
                //Proceso el requerimiento.
        $except = ['id','created_at','updated_at','deleted_at'];
        
        $datos_basicos = $request->except($except);

        try
        {
            \DB::beginTransaction();

            //Guardo los datos del requerimiento
            $model->fill($datos_basicos);
            

            $model->save();

            \DB::commit();
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            return $this->sendError($e->getMessage());
        }
        
        //$model->save();
        return $this->sendResponse($model->toArray(), trans('api.success'));
    }  

    public function destroyConciliacion($clienteId,$tramiteId,$conciliacionId, TramiteConciliacionRepository $beneRepo)
    {
        $model = $beneRepo->findWithoutFail($conciliacionId);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model->delete();

        return $this->sendResponse($conciliacionId, trans('api.success'));

    }

}
