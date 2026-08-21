<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Traits\GenerarDocumentacionTrait;
use App\Http\Controllers\Traits\TramitesTrait;
use App\Http\Requests\API\CreateUpdateAvisoClienteAPIRequest;
use App\Http\Requests\API\CreateUpdateClienteAPIRequest;
use App\Http\Requests\API\CreateUpdateObservacionClienteAPIRequest;
use App\Mail\NuevoAviso;
use App\Mail\NuevoCliente;
use App\Models\Cliente;
use App\Models\RequerimientoCliente;
use App\Models\ResponsableRequerimiento;
use App\Models\TramiteCliente;
use App\Repositories\AvisoClienteRepository;
use App\Repositories\ClienteRepository;
use App\Repositories\Criteria\CustomDataTableCriteria;
use App\Repositories\DocumentoClienteRepository;
use App\Repositories\ObservacionClienteRepository;
use App\Repositories\RequerimientoClienteRepository;
use App\Repositories\TipoTramiteRepository;
use App\Repositories\TramiteClienteRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class ClienteController
 * @package App\Http\Controllers\API
 */

class ClienteAPIController extends AppBaseController
{
    use TramitesTrait;
    //use GenerarDocumentacionTrait;
    /** @var  ClienteRepository */
    private $repository;

    public function __construct(ClienteRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the Cliente.
     * GET|HEAD /clientes
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->repository->pushCriteria(new CustomDataTableCriteria($request));
        $this->repository->pushCriteria(new RequestCriteria($request));
        
        $collection = $this->repository->with($this->_cargarRelaciones())->paginate($request->get('per_page',10));    
        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }

    private function _cargarRelaciones($grupo='all')
    {
        $relaciones = [
            'all' => [
            ],
            'documentos' => [
                'documentos' => function($query) {
                    $query->select('id','nombre','nombre_archivo','nombre_real','fecha_archivo','descripcion','cliente_id');
                },
            ],
            'observaciones' => [
                /*'observaciones' => function($query) {
                    $query->select('id','observacion','cliente_id','user_id','created_at')->orderBy('id','desc');
                },*/
                'user' => function($query) {
                    $query->select('id','name');
                }                
            ],
            'avisos' => [
                'avisos' => function($query) {
                    $query->orderBy('fecha','desc');
                },
                'avisos.user' => function($query) {
                    $query->select('id','name');
                },
                'avisos.cliente' => function($query) {
                    $query->select('id','nombre_completo');
                }
            ],            
            'requerimientos' => [
                'requerimientos.tipoTramite' => function($query) {
                    $query->select('id','nombre','tratamiento','inicia_tramite')->withTrashed();
                },
                'requerimientos.repOrigen' => function($query) {
                    $query->select('id','nombre');
                },
                'requerimientos.colega' => function($query) {
                    $query->select('id','nombre');
                },  
                'requerimientos.cliente' => function($query) {
                    $query->select('id','nombre_completo','nro_doc');
                },                                      
                'requerimientos.estado' => function($query) {
                    $query->select('id','nombre')->withTrashed();
                },                        
                'requerimientos.area' => function($query) {
                    $query->select('id','nombre');
                },   
                'requerimientos.reqNec' => function($query) {
                    $query->select('id','estado_req_id','tramite_id');
                },    
                'requerimientos.reqNec.estado' => function($query) {
                    $query->select('id','nombre');
                }, 
                'requerimientos.reqPadre' => function($query) {
                    $query->select('id','estado_req_id','nombre_causante','tipo_doc_id_causante','nro_doc_causante','domicilio_causante','estado_civil','fecha_mat_causante','fecha_conv_causante','fecha_fallecimiento_causante','hijos','req_nec_id');
                },                                                     
                'requerimientos.responsables' => function($query) {
                    $query->select('id','requerimiento_id','user_id','fecha_asignacion')->orderBy('ppal','desc')->orderBy('id','asc');
                },
                'requerimientos.responsables.user' => function($query) {
                    $query->select('id','name');
                },
            ],
            'requerimiento' => [
                'tipoTramite' => function($query) {
                    $query->select('id','nombre','tratamiento','inicia_tramite')->withTrashed();
                },
                'repOrigen' => function($query) {
                    $query->select('id','nombre');
                },
                'cliente' => function($query) {
                    $query->select('id','nombre_completo','nro_doc');
                },                
                'colega' => function($query) {
                    $query->select('id','nombre');
                },                      
                'estado' => function($query) {
                    $query->select('id','nombre')->withTrashed();
                },                        
                'area' => function($query) {
                    $query->select('id','nombre');
                },  
                'reqNec' => function($query) {
                    $query->select('id','estado_req_id','tramite_id');
                },    
                'reqNec.estado' => function($query) {
                    $query->select('id','nombre');
                },                                                    
                'responsables' => function($query) {
                    $query->select('id','requerimiento_id','user_id','fecha_asignacion')->orderBy('ppal','desc')->orderBy('id','asc');
                },
                'responsables.user' => function($query) {
                    $query->select('id','name');
                }
            ]
        ];

        return $relaciones[$grupo];    
   
    }


    /**
     * Store a newly created Cliente in storage.
     * POST /clientes
     *
     * @param CreateClienteAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateClienteAPIRequest $request)
    {
        $input = $request->all();

        $model = $this->repository->create($input);

        if ($model) {
            try {
                Mail::queue(new NuevoCliente($model));
            } catch(\Exception $e) {

            }
            
        }
        
        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified Cliente.
     * GET|HEAD /clientes/{id}
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

    /**
     * Update the specified Cliente in storage.
     * PUT/PATCH /clientes/{id}
     *
     * @param  int $id
     * @param UpdateClienteAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateClienteAPIRequest $request)
    {
        $input = $request->except('documentos','requerimientos','observaciones');

        /** @var Cliente $model */
        $model = $this->repository->with($this->_cargarRelaciones())->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified Cliente from storage.
     * DELETE /clientes/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Cliente $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        if ($model->requerimientos()->count() > 0) {
            return $this->sendError('No se puede eliminar al cliente debido a que tiene requerimientos/tramites',422);    
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

    public function storeDocumentos($id,Request $request)
    {
        $input = $request->all();

        $cliente = $this->repository->find($id);
        
        if (empty($cliente)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $cliente->documentos()->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    public function updateDocumentos($id,$oid,Request $request,DocumentoClienteRepository $docRepo)
    {
        /** @var Cliente $model */
        $model = $docRepo->findWithoutFail($oid);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model->fill($request->except('id','cliente_id','created_at','updated_at','deleted_at'));
        $model->save();
        return $this->sendResponse($model->toArray(), trans('api.success'));
    }
    
    public function destroyDocumentos($id,$oid,DocumentoClienteRepository $docRepo)
    {
        /** @var Cliente $model */
        $model = $docRepo->findWithoutFail($oid);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model->delete();

        return $this->sendResponse($id, trans('api.success'));
    }

    public function getDocumentos($id)
    {
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model->load($this->_cargarRelaciones('documentos'));
        
        return $this->sendResponse($model->documentos,trans('api.success'));
    }

    public function getObservaciones($id,Request $request,ObservacionClienteRepository $obsRepo)
    {
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $obsRepo->pushCriteria(new RequestCriteria($request));
        
        $collection = $obsRepo->with($this->_cargarRelaciones('observaciones'))->scopeQuery(function($query) use($id) {
            return $query->whereClienteId($id);
        })->paginate($request->get('per_page',10));    
        
        return $this->sendResponse($collection->toArray(), trans('api.success'));

    }


    public function storeObservaciones($id,CreateUpdateObservacionClienteAPIRequest $request)
    {
        $input = $request->except('created_at','user','user_id');
        
        $input['user_id'] = $request->user()->id;


        $cliente = $this->repository->find($id);
        
        if (empty($cliente)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $cliente->observaciones()->create($input);
        $model->load('user');

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    public function updateObservaciones($id,$oid,CreateUpdateObservacionClienteAPIRequest $request,ObservacionClienteRepository $docRepo)
    {
        /** @var Cliente $model */
        $model = $docRepo->with('user')->findWithoutFail($oid);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model->fill($request->except('id','cliente_id','created_at','updated_at','deleted_at','user'));
        $model->save();

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }
    
    public function destroyObservaciones($id,$oid,ObservacionClienteRepository $docRepo)
    {
        /** @var Cliente $model */
        $model = $docRepo->findWithoutFail($oid);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model->delete();

        return $this->sendResponse($id, trans('api.success'));
    }

    public function getAvisos($id)
    {
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model->load($this->_cargarRelaciones('avisos'));
        
        return $this->sendResponse($model->avisos,trans('api.success'));
    }


    public function storeAvisos($id,CreateUpdateAvisoClienteAPIRequest $request)
    {
        $input = $request->except('created_at','user','user_id');
        
        $input['user_id'] = $request->user()->id;


        $cliente = $this->repository->find($id);
        
        if (empty($cliente)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $cliente->avisos()->create($input);
        $model->load('user');

        try
        {
            if ($model->type === 'U' && env('APP_ENV','local') !== 'local')
            {
                Mail::queue(new NuevoAviso($model));
            }
        }
        catch(\Exception $ex) 
        {
            \Log::info('Error en envio de email de nuevo aviso: '. $e->getMessage());
        }
        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    public function updateAvisos($id,$oid,CreateUpdateAvisoClienteAPIRequest $request,AvisoClienteRepository $avisoRepo)
    {
        /** @var Cliente $model */
        $model = $avisoRepo->with('user')->findWithoutFail($oid);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model->fill($request->except('id','cliente_id','created_at','updated_at','deleted_at','user'));
        $model->save();

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }
    
    public function descartarAvisos($id,$aid,AvisoClienteRepository $avisoRepo)
    {
        /** @var Cliente $model */
        $model = $avisoRepo->findWithoutFail($aid);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model->descartado = true;
        $model->save();

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    public function destroyAvisos($id,$oid,AvisoClienteRepository $avisoRepo)
    {
        /** @var Cliente $model */
        $model = $avisoRepo->findWithoutFail($oid);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model->delete();

        return $this->sendResponse($id, trans('api.success'));
    }

    public function getRequerimientos($id)
    {
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model->load($this->_cargarRelaciones('requerimientos'));
        
        return $this->sendResponse($model->requerimientos,trans('api.success'));
    }

    public function enviarDocRequerimiento($clienteId,$idRequerimiento, RequerimientoClienteRepository $reqRepo) 
    {
        
        try
        {
            $model = $reqRepo->with('responsables.user','cliente')->findWithoutFail($idRequerimiento);

            if (empty($model)) {
                return $this->sendError(trans('api.not_found'));
            }
            
            if (env('APP_ENV','local') !== 'local') {
                \Mail::queue(new \App\Mail\DocumentacionReq($model));
            }
            
            //$contents = $this->getContenidoPorArea($model);
            //return $contents;

            return response()->json([]);


        }
        catch (Illuminate\Filesystem\FileNotFoundException $exception)
        {
            return $this->sendError($exception->getMessage());
        }       

    }

    public function storeRequerimientos($id,Request $request, TipoTramiteRepository $tipoTramiteRepo)
    {
        $model = new RequerimientoCliente();
        //Proceso el requerimiento.
        $except = ['id','area','colega','created_at','updated_at','deleted_at','estado','rep_origen','tipo_tramite','responsables'];
        
        $datos_basicos = $request->except($except);

        $tipo_tramite = $request->get('tipo_tramite');

        if ($tipo_tramite['tratamiento'] !== 'PENSION')
        {
            $datos_basicos['nombre_causante'] = NULL;
            $datos_basicos['tipo_doc_id_causante'] = NULL;
            $datos_basicos['nro_doc_causante'] = NULL;
            $datos_basicos['domicilio_causante'] = NULL;
            $datos_basicos['estado_civil'] = NULL;
            $datos_basicos['fecha_mat_causante'] = NULL;
            $datos_basicos['fecha_conv_causante'] = NULL;
            $datos_basicos['fecha_fallecimiento_causante'] = NULL;
            $datos_basicos['hijos'] = NULL;
        }

        try
        {
            \DB::beginTransaction();

            //Guardo los datos del requerimiento
            $model->fill($datos_basicos);
            
            //Guardo los responsables. El primero va a ser el ppal. Los restantes seran secundarios
            $responsables = $request->get('responsables');
            //$model->responsables()->delete();

            //Cargo los ids que voy a modificar para hacer update en la marca de ppal
            $resp_ids_updates = [];
            $resp_id_ppal = null;
            $resp_add = [];
            $resp_send_email = [];
            foreach ($responsables as $index => $resp) 
            {
                //Es nuevo
                $resp_add[] = [
                    //'requerimiento_id' => $model->id,
                    'user_id' => $resp['user_id'],
                    'fecha_asignacion' => \Carbon\Carbon::today()->format('d/m/Y'),
                    'ppal' => $index === 0
                ];
                $resp_send_email[] = $resp['user_id'];
            }

            if ($tipo_tramite['tratamiento'] === 'PENSION' && $datos_basicos['estado_civil'] === 'CO')            
            {
                //Si es PENSION y convive, tengo que crear un req de acreditacion de conv.

                $tipoTramiteAC = $tipoTramiteRepo->findWhere(['tratamiento' => 'ACRED_CONV', 'area_id' => $request->get('area_id')])->first();
                if (!$tipoTramiteAC)
                {
                    throw new \Exception("No existe un Tipo de Tramite para Acreditacion de convivencia", 1);
                }
                $datos_basicos['tipo_tramite_id'] = $tipoTramiteAC->id;
                $reqAcrConv = new RequerimientoCliente();

                $reqAcrConv->fill($datos_basicos);
                $reqAcrConv->save();
             
                //Inserto los nuevos responsables
                foreach ($resp_add as $nuevo) {
                    $reqAcrConv->responsables()->save(new ResponsableRequerimiento($nuevo));    
                }                

                $model->req_nec_id = $reqAcrConv->id;
            }

            $model->save();

            //Inserto los nuevos responsables
            foreach ($resp_add as $nuevo) {
                $model->responsables()->save(new ResponsableRequerimiento($nuevo));    
            }


            $model->load($this->_cargarRelaciones('requerimiento'));

            if (count($resp_send_email) > 0) 
            {
                try {
                    if (env('APP_ENV','local') !== 'local') {
                        \Mail::queue(new \App\Mail\AsignarRespReq($model,$resp_send_email));
                    }
                } catch ( \Exception $ex ) {}
                
            }

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

    public function updateRequerimientos($id,$rid,Request $request,RequerimientoClienteRepository $reqRepo,TipoTramiteRepository $tipoTramiteRepo)
    {
        /** @var Cliente $model */
        $model = $reqRepo->findWithoutFail($rid);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        //Proceso el requerimiento.
        $except = ['id','area','colega','created_at','updated_at','deleted_at','estado','rep_origen','tipo_tramite','responsables','req_nec_id'];
        
        $datos_basicos = $request->except($except);

        $tipo_tramite = $request->get('tipo_tramite');

        if ($tipo_tramite['tratamiento'] !== 'PENSION')
        {
            $datos_basicos['nombre_causante'] = NULL;
            $datos_basicos['tipo_doc_id_causante'] = NULL;
            $datos_basicos['nro_doc_causante'] = NULL;
            $datos_basicos['domicilio_causante'] = NULL;
            $datos_basicos['estado_civil'] = NULL;
            $datos_basicos['fecha_mat_causante'] = NULL;
            $datos_basicos['fecha_conv_causante'] = NULL;
            $datos_basicos['fecha_fallecimiento_causante'] = NULL;
            $datos_basicos['hijos'] = NULL;
        }

        try
        {
            \DB::beginTransaction();

            //Guardo los datos del requerimiento
            $model->fill($datos_basicos);
            $model->save();
            //Guardo los responsables. El primero va a ser el ppal. Los restantes seran secundarios
            $responsables = $request->get('responsables');
            //\Log::info($responsables);
            //Cargo los ids que voy a modificar para hacer update en la marca de ppal
            $resp_ids_updates = [];
            $resp_id_ppal = null;
            $resp_add = [];
            $resp_send_email = [];
            foreach ($responsables as $index => $resp) 
            {
                if (isset($resp['id'])) 
                {
                    //Ya existe
                    if ($index === 0) 
                    {
                        //Es ppal
                        $resp_id_ppal = $resp['id'];
                    }

                    //Es update, no tengo q eliminar
                    $resp_ids_updates[] = $resp['id'];                     
                }   
                else 
                {
                    //Es nuevo
                    $resp_add[] = [
                        //'requerimiento_id' => $model->id,
                        'user_id' => $resp['user_id'],
                        'fecha_asignacion' => null,
                        'ppal' => $index === 0
                    ];
                    $resp_send_email[] = $resp['user_id'];
                }             
            }

            //Borro los responsables que no sean para actualizar
            $model->responsables()->whereNotIn('id',$resp_ids_updates)->delete();

            //Marco a todos los responsables como suplentes
            $model->responsables()->update(['ppal' => false]);
            
            //Inserto los nuevos responsables
            foreach ($resp_add as $nuevo) {
                $model->responsables()->save(new ResponsableRequerimiento($nuevo));    
            }
            
            //Si hay un responsable ppal
            if ($resp_id_ppal)
            {
                $model->responsables()->where('id',$resp_id_ppal)->update(['ppal' => true]);
            }

            
            if ($tipo_tramite['tratamiento'] === 'PENSION' && $datos_basicos['estado_civil'] === 'CO')            
            {
                //Si es PENSION y convive, tengo que crear un req de acreditacion de conv.
                if (!$model->req_nec_id)
                {
                    $tipoTramiteAC = $tipoTramiteRepo->findWhere(['tratamiento' => 'ACRED_CONV', 'area_id' => $request->get('area_id')])->first();
                    if (!$tipoTramiteAC)
                    {
                        throw new \Exception("No existe un Tipo de Tramite para Acreditacion de convivencia", 1);
                    }
                    $datos_basicos['tipo_tramite_id'] = $tipoTramiteAC->id;
                    $reqAcrConv = new RequerimientoCliente();

                    $reqAcrConv->fill($datos_basicos);
                    $reqAcrConv->save();
                 
                    //Inserto los nuevos responsables
                    foreach ($responsables as $index => $resp) 
                    {
                        //Es nuevo
                        $reqAcrConv->responsables()->save(new ResponsableRequerimiento([
                            'user_id' => $resp['user_id'],
                            'fecha_asignacion' => \Carbon\Carbon::today()->format('d/m/Y'),
                            'ppal' => $index === 0
                        ]));    
                    }                

                    $model->req_nec_id = $reqAcrConv->id;
                    $model->save();                    
                }
            }
            else if($tipo_tramite['tratamiento'] !== 'PENSION')
            {
                $model->req_nec_id = null;
                $model->save();
            }               


            $model->load($this->_cargarRelaciones('requerimiento'));

            if (count($resp_send_email) > 0) 
            {
                try {
                    if (env('APP_ENV','local') !== 'local') {
                        \Mail::queue(new \App\Mail\AsignarRespReq($model,$resp_send_email));
                    }
                } catch (\Exception $ex) {}
            }

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

    public function asignarTramiteRequerimientos($id,$rid,Request $request,RequerimientoClienteRepository $reqRepo)
    {
        /** @var Cliente $model */
        $model = $reqRepo->findWithoutFail($rid);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }


        try
        {
            \DB::beginTransaction();
            $model->tramite_id = $request->get('tramite_id');
            $model->estado_req_id = 54;
            $model->save();
            $model->load($this->_cargarRelaciones('requerimiento'));

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

    public function destroyRequerimientos($id,$rid,RequerimientoClienteRepository $reqRepo)
    {
        /** @var Cliente $model */
        $model = $reqRepo->findWithoutFail($rid);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        if ($model->tramite_id) {
            return $this->sendError('No se puede eliminar el requerimiento debido a que tiene un trámite asignado',422);    
        }

        $model->delete();

        return $this->sendResponse($id, trans('api.success'));
    }

    public function getTramites($id,$tipo="")
    {
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        
        
        switch ($tipo) {
            case 'historicos':
                $model->load($this->_cargarRelacionesTramites('historicos'));
                $data = $model->tramitesHistoricos;
                break;
            case 'actuales':
                $model->load($this->_cargarRelacionesTramites('actuales'));
                $data = $model->tramitesActuales;
                break;
            default: 
                $model->load($this->_cargarRelacionesTramites('tramites'));
                $data = $model->tramites;
                break;
        }

        return $this->sendResponse($data,trans('api.success'));
    }



    public function getBeneficiosPorTramite($tId,TramiteClienteRepository $traRepo)
    {
        $model = $traRepo->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model->load('beneficios');
        
        return $this->sendResponse($model->beneficios,trans('api.success'));        
    }

    public function getConciliacionPorTramite($tId,TramiteClienteRepository $traRepo)
    {
        $model = $traRepo->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model->load('conciliacion');
        
        return $this->sendResponse($model->conciliacion,trans('api.success'));        
    }

    public function storeTramites($id,Request $request,RequerimientoClienteRepository $reqRepo)
    {
        try 
        {
            $model = $this->storeTramite($request,$reqRepo);
            return $this->sendResponse($model->toArray(), trans('api.success'));    
        }
        catch( \Exception $ex )
        {
            return $this->sendError($ex->getMessage());
        } 
    }

    public function updateTramites($id,$tid,Request $request,TramiteClienteRepository $traRepo,RequerimientoClienteRepository $reqRepo)
    {
        try 
        {
            $model = $this->updateTramite($tid,$request,$traRepo,$reqRepo);
            return $this->sendResponse($model->toArray(), trans('api.success'));    
        }
        catch( \Exception $ex )
        {
            return $this->sendError($ex->getMessage());
        }
    }


    public function destroyTramites($id,$tid,TramiteClienteRepository $reqRepo)
    {
        /** @var Cliente $model */
        $model = $reqRepo->findWithoutFail($tid);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model->delete();

        return $this->sendResponse($id, trans('api.success'));
    }

    private function tratarReajusteMovilidad($tramite,$datos,RequerimientoClienteRepository $reqRepo) 
    {
        $except = ['id','area','colega','created_at','updated_at','deleted_at','estado','rep_origen','tipo_tramite','responsables'];
        $req = $reqRepo->with('responsables')->findWithoutFail($tramite->requerimiento_id);
        $reqData = \Illuminate\Support\Arr::except($req->toArray(),$except);
        $newReq = new RequerimientoCliente();
        $newReq->fill($reqData);
        $newReq->save();

        
        foreach ($req->responsables as $resp) {

            $resp->requerimiento_id = $newReq->id;
            //\Log::info($resp);
            $newReq->responsables()->create(\Illuminate\Support\Arr::except($resp->toArray(),['created_at','updated_at','deleted_at']));
        }

        $newTramite = new TramiteCliente();
        $newTramite->fill($datos);
        $newTramite->fecha_remision_vto = \Carbon\Carbon::createFromFormat('d/m/Y',$newTramite->fecha_inicio)->addWeekdays(120)->format('d/m/Y');
        $newTramite->fecha_remision = $newTramite->fecha_inicio;
        $newTramite->area_id = $reqData['area_id'];
        $newTramite->cliente_id = $reqData['cliente_id'];
        $newTramite->tramite_ant_id = $tramite->id;
        $newTramite->requerimiento_id = $newReq->id;
        $newTramite->estado_tramite_id = 55;//Iniciado
        $newTramite->save();

        $newReq->tramite_id = $newTramite->id;
        $newReq->save();

        return $newTramite;
    } 

}
