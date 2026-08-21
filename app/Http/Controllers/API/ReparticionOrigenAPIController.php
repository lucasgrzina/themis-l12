<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateReparticionOrigenAPIRequest;
use App\Models\ReparticionOrigen;
use App\Repositories\ReparticionOrigenRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class ReparticionOrigenController
 * @package App\Http\Controllers\API
 */

class ReparticionOrigenAPIController extends AppBaseController
{
    /** @var  ReparticionOrigenRepository */
    private $repository;

    public function __construct(ReparticionOrigenRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the ReparticionOrigen.
     * GET|HEAD /reparticionOrigens
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
     * Store a newly created ReparticionOrigen in storage.
     * POST /reparticionOrigens
     *
     * @param CreateReparticionOrigenAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateReparticionOrigenAPIRequest $request)
    {
        $input = $request->except('area');

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified ReparticionOrigen.
     * GET|HEAD /reparticionOrigens/{id}
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
     * Update the specified ReparticionOrigen in storage.
     * PUT/PATCH /reparticionOrigens/{id}
     *
     * @param  int $id
     * @param UpdateReparticionOrigenAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateReparticionOrigenAPIRequest $request)
    {
        $input = $request->except('area');

        /** @var ReparticionOrigen $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified ReparticionOrigen from storage.
     * DELETE /reparticionOrigens/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var ReparticionOrigen $model */
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
