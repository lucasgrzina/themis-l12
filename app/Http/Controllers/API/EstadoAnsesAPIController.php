<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateEstadoAnsesAPIRequest;
use App\Models\EstadoAnses;
use App\Repositories\EstadoAnsesRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class EstadoAnsesController
 * @package App\Http\Controllers\API
 */

class EstadoAnsesAPIController extends AppBaseController
{
    /** @var  EstadoAnsesRepository */
    private $repository;

    public function __construct(EstadoAnsesRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the EstadoAnses.
     * GET|HEAD /estadoAnses
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
     * Store a newly created EstadoAnses in storage.
     * POST /estadoAnses
     *
     * @param CreateEstadoAnsesAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateEstadoAnsesAPIRequest $request)
    {
        $input = $request->all();

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified EstadoAnses.
     * GET|HEAD /estadoAnses/{id}
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
     * Update the specified EstadoAnses in storage.
     * PUT/PATCH /estadoAnses/{id}
     *
     * @param  int $id
     * @param UpdateEstadoAnsesAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateEstadoAnsesAPIRequest $request)
    {
        $input = $request->all();

        /** @var EstadoAnses $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified EstadoAnses from storage.
     * DELETE /estadoAnses/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var EstadoAnses $model */
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
