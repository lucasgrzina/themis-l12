<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateEstadoTramiteAPIRequest;
use App\Models\EstadoTramite;
use App\Repositories\EstadoTramiteRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class EstadoTramiteController
 * @package App\Http\Controllers\API
 */

class EstadoTramiteAPIController extends AppBaseController
{
    /** @var  EstadoTramiteRepository */
    private $repository;

    public function __construct(EstadoTramiteRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the EstadoTramite.
     * GET|HEAD /estadoTramites
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->repository->pushCriteria(new CustomDataTableCriteria($request));
        $this->repository->pushCriteria(new RequestCriteria($request));
        $collection = $this->repository->with('area')->paginate($request->get('per_page',10));

        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }

    /**
     * Store a newly created EstadoTramite in storage.
     * POST /estadoTramites
     *
     * @param CreateEstadoTramiteAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateEstadoTramiteAPIRequest $request)
    {
        $input = $request->except('area');

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified EstadoTramite.
     * GET|HEAD /estadoTramites/{id}
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
     * Update the specified EstadoTramite in storage.
     * PUT/PATCH /estadoTramites/{id}
     *
     * @param  int $id
     * @param UpdateEstadoTramiteAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateEstadoTramiteAPIRequest $request)
    {
        $input = $request->except('area');

        /** @var EstadoTramite $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified EstadoTramite from storage.
     * DELETE /estadoTramites/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var EstadoTramite $model */
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
