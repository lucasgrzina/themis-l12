<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateEstadoExpedienteAPIRequest;
use App\Models\EstadoExpediente;
use App\Repositories\EstadoExpedienteRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class EstadoExpedienteController
 * @package App\Http\Controllers\API
 */

class EstadoExpedienteAPIController extends AppBaseController
{
    /** @var  EstadoExpedienteRepository */
    private $repository;

    public function __construct(EstadoExpedienteRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the EstadoExpediente.
     * GET|HEAD /estadoExpedientes
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
     * Store a newly created EstadoExpediente in storage.
     * POST /estadoExpedientes
     *
     * @param CreateEstadoExpedienteAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateEstadoExpedienteAPIRequest $request)
    {
        $input = $request->all();

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified EstadoExpediente.
     * GET|HEAD /estadoExpedientes/{id}
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
     * Update the specified EstadoExpediente in storage.
     * PUT/PATCH /estadoExpedientes/{id}
     *
     * @param  int $id
     * @param UpdateEstadoExpedienteAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateEstadoExpedienteAPIRequest $request)
    {
        $input = $request->all();

        /** @var EstadoExpediente $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified EstadoExpediente from storage.
     * DELETE /estadoExpedientes/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var EstadoExpediente $model */
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
