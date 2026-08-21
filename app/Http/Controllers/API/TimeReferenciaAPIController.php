<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateTimeReferenciaAPIRequest;
use App\Models\TimeReferencia;
use App\Repositories\TimeReferenciaRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class TimeReferenciaController
 * @package App\Http\Controllers\API
 */

class TimeReferenciaAPIController extends AppBaseController
{
    /** @var  TimeReferenciaRepository */
    private $repository;

    public function __construct(TimeReferenciaRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the TimeReferencia.
     * GET|HEAD /timeReferencias
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
     * Store a newly created TimeReferencia in storage.
     * POST /timeReferencias
     *
     * @param CreateTimeReferenciaAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateTimeReferenciaAPIRequest $request)
    {
        $input = $request->all();

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified TimeReferencia.
     * GET|HEAD /timeReferencias/{id}
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
     * Update the specified TimeReferencia in storage.
     * PUT/PATCH /timeReferencias/{id}
     *
     * @param  int $id
     * @param UpdateTimeReferenciaAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateTimeReferenciaAPIRequest $request)
    {
        $input = $request->all();

        /** @var TimeReferencia $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified TimeReferencia from storage.
     * DELETE /timeReferencias/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var TimeReferencia $model */
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
