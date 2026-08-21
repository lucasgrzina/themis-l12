<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateProvinciaAPIRequest;
use App\Models\Provincia;
use App\Repositories\ProvinciaRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class ProvinciaController
 * @package App\Http\Controllers\API
 */

class ProvinciaAPIController extends AppBaseController
{
    /** @var  ProvinciaRepository */
    private $repository;

    public function __construct(ProvinciaRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the Provincia.
     * GET|HEAD /provincias
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
     * Store a newly created Provincia in storage.
     * POST /provincias
     *
     * @param CreateProvinciaAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateProvinciaAPIRequest $request)
    {
        $input = $request->all();

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified Provincia.
     * GET|HEAD /provincias/{id}
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
     * Update the specified Provincia in storage.
     * PUT/PATCH /provincias/{id}
     *
     * @param  int $id
     * @param UpdateProvinciaAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateProvinciaAPIRequest $request)
    {
        $input = $request->all();

        /** @var Provincia $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified Provincia from storage.
     * DELETE /provincias/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Provincia $model */
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
