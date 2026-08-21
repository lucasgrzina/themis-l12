<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateTipoSociedadAPIRequest;
use App\Models\TipoSociedad;
use App\Repositories\TipoSociedadRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class TipoSociedadController
 * @package App\Http\Controllers\API
 */

class TipoSociedadAPIController extends AppBaseController
{
    /** @var  TipoSociedadRepository */
    private $repository;

    public function __construct(TipoSociedadRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the TipoSociedad.
     * GET|HEAD /tipoSociedads
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

    /**
     * Store a newly created TipoSociedad in storage.
     * POST /tipoSociedads
     *
     * @param CreateTipoSociedadAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateTipoSociedadAPIRequest $request)
    {
        $input = $request->all();

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified TipoSociedad.
     * GET|HEAD /tipoSociedads/{id}
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
     * Update the specified TipoSociedad in storage.
     * PUT/PATCH /tipoSociedads/{id}
     *
     * @param  int $id
     * @param UpdateTipoSociedadAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateTipoSociedadAPIRequest $request)
    {
        $input = $request->all();

        /** @var TipoSociedad $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified TipoSociedad from storage.
     * DELETE /tipoSociedads/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var TipoSociedad $model */
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
