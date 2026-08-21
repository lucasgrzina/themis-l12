<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateEstadoRequerimientoAPIRequest;
use App\Models\EstadoRequerimiento;
use App\Repositories\EstadoRequerimientoRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class EstadoRequerimientoController
 * @package App\Http\Controllers\API
 */

class EstadoRequerimientoAPIController extends AppBaseController
{
    /** @var  EstadoRequerimientoRepository */
    private $repository;

    public function __construct(EstadoRequerimientoRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the EstadoRequerimiento.
     * GET|HEAD /estadoRequerimientos
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
     * Store a newly created EstadoRequerimiento in storage.
     * POST /estadoRequerimientos
     *
     * @param CreateEstadoRequerimientoAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateEstadoRequerimientoAPIRequest $request)
    {
        $input = $request->all();

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified EstadoRequerimiento.
     * GET|HEAD /estadoRequerimientos/{id}
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
     * Update the specified EstadoRequerimiento in storage.
     * PUT/PATCH /estadoRequerimientos/{id}
     *
     * @param  int $id
     * @param UpdateEstadoRequerimientoAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateEstadoRequerimientoAPIRequest $request)
    {
        $input = $request->except('area');

        /** @var EstadoRequerimiento $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified EstadoRequerimiento from storage.
     * DELETE /estadoRequerimientos/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var EstadoRequerimiento $model */
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
