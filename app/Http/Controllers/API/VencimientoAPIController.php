<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateVencimientoAPIRequest;
use App\Models\Vencimiento;
use App\Repositories\VencimientoRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class VencimientoController
 * @package App\Http\Controllers\API
 */

class VencimientoAPIController extends AppBaseController
{
    /** @var  VencimientoRepository */
    private $repository;

    public function __construct(VencimientoRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the Vencimiento.
     * GET|HEAD /vencimientos
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
     * Store a newly created Vencimiento in storage.
     * POST /vencimientos
     *
     * @param CreateVencimientoAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateVencimientoAPIRequest $request)
    {
        $input = $request->all();

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified Vencimiento.
     * GET|HEAD /vencimientos/{id}
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
     * Update the specified Vencimiento in storage.
     * PUT/PATCH /vencimientos/{id}
     *
     * @param  int $id
     * @param UpdateVencimientoAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateVencimientoAPIRequest $request)
    {
        $input = $request->all();

        /** @var Vencimiento $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified Vencimiento from storage.
     * DELETE /vencimientos/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Vencimiento $model */
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
