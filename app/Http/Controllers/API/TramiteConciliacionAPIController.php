<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateTramiteConciliacionAPIRequest;
use App\Models\TramiteConciliacion;
use App\Repositories\TramiteConciliacionRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class TramiteConciliacionController
 * @package App\Http\Controllers\API
 */

class TramiteConciliacionAPIController extends AppBaseController
{
    /** @var  TramiteConciliacionRepository */
    private $repository;

    public function __construct(TramiteConciliacionRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the TramiteConciliacion.
     * GET|HEAD /tramiteConciliacion
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
     * Store a newly created TramiteConciliacion in storage.
     * POST /tramiteConciliacion
     *
     * @param CreateTramiteConciliacionAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateTramiteConciliacionAPIRequest $request)
    {
        $input = $request->all();

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified TramiteConciliacion.
     * GET|HEAD /tramiteConciliacion/{id}
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
     * Update the specified TramiteConciliacion in storage.
     * PUT/PATCH /tramiteConciliacion/{id}
     *
     * @param  int $id
     * @param UpdateTramiteConciliacionAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateTramiteConciliacionAPIRequest $request)
    {
        $input = $request->all();

        /** @var TramiteConciliacion $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified TramiteConciliacion from storage.
     * DELETE /tramiteConciliacion/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var TramiteConciliacion $model */
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
