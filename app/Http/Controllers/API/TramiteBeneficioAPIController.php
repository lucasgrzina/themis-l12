<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateTramiteBeneficioAPIRequest;
use App\Models\TramiteBeneficio;
use App\Repositories\TramiteBeneficioRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class TramiteBeneficioController
 * @package App\Http\Controllers\API
 */

class TramiteBeneficioAPIController extends AppBaseController
{
    /** @var  TramiteBeneficioRepository */
    private $repository;

    public function __construct(TramiteBeneficioRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the TramiteBeneficio.
     * GET|HEAD /tramiteBeneficios
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
     * Store a newly created TramiteBeneficio in storage.
     * POST /tramiteBeneficios
     *
     * @param CreateTramiteBeneficioAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateTramiteBeneficioAPIRequest $request)
    {
        $input = $request->all();

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified TramiteBeneficio.
     * GET|HEAD /tramiteBeneficios/{id}
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
     * Update the specified TramiteBeneficio in storage.
     * PUT/PATCH /tramiteBeneficios/{id}
     *
     * @param  int $id
     * @param UpdateTramiteBeneficioAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateTramiteBeneficioAPIRequest $request)
    {
        $input = $request->all();

        /** @var TramiteBeneficio $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified TramiteBeneficio from storage.
     * DELETE /tramiteBeneficios/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var TramiteBeneficio $model */
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
