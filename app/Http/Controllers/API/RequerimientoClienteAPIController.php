<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateRequerimientoClienteAPIRequest;
use App\Models\RequerimientoCliente;
use App\Repositories\RequerimientoClienteRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class RequerimientoClienteController
 * @package App\Http\Controllers\API
 */

class RequerimientoClienteAPIController extends AppBaseController
{
    /** @var  RequerimientoClienteRepository */
    private $repository;

    public function __construct(RequerimientoClienteRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the RequerimientoCliente.
     * GET|HEAD /requerimientoClientes
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
     * Store a newly created RequerimientoCliente in storage.
     * POST /requerimientoClientes
     *
     * @param CreateRequerimientoClienteAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateRequerimientoClienteAPIRequest $request)
    {
        $input = $request->all();

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified RequerimientoCliente.
     * GET|HEAD /requerimientoClientes/{id}
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
     * Update the specified RequerimientoCliente in storage.
     * PUT/PATCH /requerimientoClientes/{id}
     *
     * @param  int $id
     * @param UpdateRequerimientoClienteAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateRequerimientoClienteAPIRequest $request)
    {
        $input = $request->all();

        /** @var RequerimientoCliente $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified RequerimientoCliente from storage.
     * DELETE /requerimientoClientes/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var RequerimientoCliente $model */
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
