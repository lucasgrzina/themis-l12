<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdatePaisAPIRequest;
use App\Models\Pais;
use App\Repositories\PaisRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class PaisController
 * @package App\Http\Controllers\API
 */

class PaisAPIController extends AppBaseController
{
    /** @var  PaisRepository */
    private $repository;

    public function __construct(PaisRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the Pais.
     * GET|HEAD /pais
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
     * Store a newly created Pais in storage.
     * POST /pais
     *
     * @param CreatePaisAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdatePaisAPIRequest $request)
    {
        $input = $request->all();

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified Pais.
     * GET|HEAD /pais/{id}
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
     * Update the specified Pais in storage.
     * PUT/PATCH /pais/{id}
     *
     * @param  int $id
     * @param UpdatePaisAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdatePaisAPIRequest $request)
    {
        $input = $request->all();

        /** @var Pais $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified Pais from storage.
     * DELETE /pais/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Pais $model */
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
