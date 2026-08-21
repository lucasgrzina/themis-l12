<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateTipoAporteAPIRequest;
use App\Models\TipoAporte;
use App\Repositories\TipoAporteRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class TipoAporteController
 * @package App\Http\Controllers\API
 */

class TipoAporteAPIController extends AppBaseController
{
    /** @var  TipoAporteRepository */
    private $repository;

    public function __construct(TipoAporteRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the TipoAporte.
     * GET|HEAD /tipoAportes
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
                $query->select('id','doc_requerida_id','tipo_aporte_id');
            },
            'doc_requerida.doc' => function($query) {
                $query->select('id','nombre','descripcion');
            }
        ])->paginate($request->get('per_page',10));

        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }

    /**
     * Store a newly created TipoAporte in storage.
     * POST /tipoAportes
     *
     * @param CreateTipoAporteAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateTipoAporteAPIRequest $request)
    {
        $input = $request->except('doc_requerida');

        $model = $this->repository->create($input);

        $docRequerida = $request->get('doc_requerida',[]);
        
        $model->doc_requerida()->delete();
        $model->doc_requerida()->createMany($docRequerida);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified TipoAporte.
     * GET|HEAD /tipoAportes/{id}
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
     * Update the specified TipoAporte in storage.
     * PUT/PATCH /tipoAportes/{id}
     *
     * @param  int $id
     * @param UpdateTipoAporteAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateTipoAporteAPIRequest $request)
    {
        $input = $request->except('doc_requerida');

        /** @var TipoTramite $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        $docRequerida = $request->get('doc_requerida',[]);
        
        $model->doc_requerida()->delete();
        $model->doc_requerida()->createMany($docRequerida);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified TipoAporte from storage.
     * DELETE /tipoAportes/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var TipoAporte $model */
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
