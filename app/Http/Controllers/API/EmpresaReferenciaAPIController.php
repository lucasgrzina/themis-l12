<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateEmpresaReferenciaAPIRequest;
use App\Models\EmpresaReferencia;
use App\Repositories\EmpresaReferenciaRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class EmpresaReferenciaController
 * @package App\Http\Controllers\API
 */

class EmpresaReferenciaAPIController extends AppBaseController
{
    /** @var  EmpresaReferenciaRepository */
    private $repository;

    public function __construct(EmpresaReferenciaRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the EmpresaReferencia.
     * GET|HEAD /empresaReferencias
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
     * Store a newly created EmpresaReferencia in storage.
     * POST /empresaReferencias
     *
     * @param CreateEmpresaReferenciaAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateEmpresaReferenciaAPIRequest $request)
    {
        $input = $request->all();

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified EmpresaReferencia.
     * GET|HEAD /empresaReferencias/{id}
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
     * Update the specified EmpresaReferencia in storage.
     * PUT/PATCH /empresaReferencias/{id}
     *
     * @param  int $id
     * @param UpdateEmpresaReferenciaAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateEmpresaReferenciaAPIRequest $request)
    {
        $input = $request->all();

        /** @var EmpresaReferencia $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified EmpresaReferencia from storage.
     * DELETE /empresaReferencias/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var EmpresaReferencia $model */
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
