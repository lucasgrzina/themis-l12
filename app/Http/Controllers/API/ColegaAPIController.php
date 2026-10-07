<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateColegaAPIRequest;
use App\Models\Colega;
use App\Repositories\ColegaRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class ColegaController
 * @package App\Http\Controllers\API
 */

class ColegaAPIController extends AppBaseController
{
    /** @var  ColegaRepository */
    private $repository;

    public function __construct(ColegaRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the Colega.
     * GET|HEAD /colegas
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
     * Store a newly created Colega in storage.
     * POST /colegas
     *
     * @param CreateColegaAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateColegaAPIRequest $request)
    {
        $input = $request->all();

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified Colega.
     * GET|HEAD /colegas/{id}
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
     * Update the specified Colega in storage.
     * PUT/PATCH /colegas/{id}
     *
     * @param  int $id
     * @param UpdateColegaAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateColegaAPIRequest $request)
    {
        $input = $request->all();

        /** @var Colega $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified Colega from storage.
     * DELETE /colegas/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Colega $model */
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
