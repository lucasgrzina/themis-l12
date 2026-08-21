<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateTramiteExpedienteAPIRequest;
use App\Models\TramiteExpediente;
use App\Repositories\TramiteExpedienteRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class TramiteExpedienteController
 * @package App\Http\Controllers\API
 */

class TramiteExpedienteAPIController extends AppBaseController
{
    /** @var  TramiteExpedienteRepository */
    private $repository;

    public function __construct(TramiteExpedienteRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the TramiteExpediente.
     * GET|HEAD /tramiteExpedientes
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
     * Store a newly created TramiteExpediente in storage.
     * POST /tramiteExpedientes
     *
     * @param CreateTramiteExpedienteAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateTramiteExpedienteAPIRequest $request)
    {
        $input = $request->all();

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified TramiteExpediente.
     * GET|HEAD /tramiteExpedientes/{id}
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
     * Update the specified TramiteExpediente in storage.
     * PUT/PATCH /tramiteExpedientes/{id}
     *
     * @param  int $id
     * @param UpdateTramiteExpedienteAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateTramiteExpedienteAPIRequest $request)
    {
        $input = $request->all();

        /** @var TramiteExpediente $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified TramiteExpediente from storage.
     * DELETE /tramiteExpedientes/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var TramiteExpediente $model */
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
