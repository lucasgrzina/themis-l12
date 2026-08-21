<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateAreaAPIRequest;
use App\Models\Area;
use App\Repositories\AreaRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class AreaController
 * @package App\Http\Controllers\API
 */

class AreaAPIController extends AppBaseController
{
    /** @var  AreaRepository */
    private $repository;

    public function __construct(AreaRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the Area.
     * GET|HEAD /areas
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
     * Store a newly created Area in storage.
     * POST /areas
     *
     * @param CreateAreaAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateAreaAPIRequest $request)
    {
        $input = $request->all();

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified Area.
     * GET|HEAD /areas/{id}
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
     * Update the specified Area in storage.
     * PUT/PATCH /areas/{id}
     *
     * @param  int $id
     * @param UpdateAreaAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateAreaAPIRequest $request)
    {
        $input = $request->all();

        /** @var Area $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified Area from storage.
     * DELETE /areas/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Area $model */
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
