<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateTipoTramiteAPIRequest;
use App\Models\TipoTramite;
use App\Repositories\TipoTramiteRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class TipoTramiteController
 * @package App\Http\Controllers\API
 */

class TipoTramiteAPIController extends AppBaseController
{
    /** @var  TipoTramiteRepository */
    private $repository;

    public function __construct(TipoTramiteRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the TipoTramite.
     * GET|HEAD /tipoTramites
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->repository->pushCriteria(new CustomDataTableCriteria($request));
        $this->repository->pushCriteria(new RequestCriteria($request));

        $collection = $this->repository->with([
            'doc_requerida' => function($query) {
                $query->select('id','doc_requerida_id','tipo_tramite_id');
            },
            'doc_requerida.doc' => function($query) {
                $query->select('id','nombre','descripcion');
            },
            'area' => function($query) {
                $query->select('id','nombre');
            }
        ])->paginate($request->get('per_page',10));

        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }

    /**
     * Store a newly created TipoTramite in storage.
     * POST /tipoTramites
     *
     * @param CreateTipoTramiteAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateTipoTramiteAPIRequest $request)
    {
        $input = $request->except('doc_requerida');
        $model = $this->repository->create($input);

        $docRequerida = $request->get('doc_requerida',[]);
        
        $model->doc_requerida()->delete();
        $model->doc_requerida()->createMany($docRequerida);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified TipoTramite.
     * GET|HEAD /tipoTramites/{id}
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
     * Update the specified TipoTramite in storage.
     * PUT/PATCH /tipoTramites/{id}
     *
     * @param  int $id
     * @param UpdateTipoTramiteAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateTipoTramiteAPIRequest $request)
    {
        $input = $request->except('doc_requerida','area');

        /** @var TipoTramite $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        $docRequerida = $request->get('doc_requerida',[]);
        
        //\Log::info(print_r($docRequerida,true));

        $model->doc_requerida()->delete();
        $model->doc_requerida()->createMany($docRequerida);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified TipoTramite from storage.
     * DELETE /tipoTramites/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var TipoTramite $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        if ($model->requerimientos()->count() > 0)
        {
            return $this->sendError('No se puede eliminar debido a que tiene requerimientos asociados');       
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
