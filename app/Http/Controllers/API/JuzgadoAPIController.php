<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateJuzgadoAPIRequest;
use App\Models\Juzgado;
use App\Repositories\JuzgadoRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class JuzgadoController
 * @package App\Http\Controllers\API
 */

class JuzgadoAPIController extends AppBaseController
{
    /** @var  JuzgadoRepository */
    private $repository;

    public function __construct(JuzgadoRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the Juzgado.
     * GET|HEAD /juzgados
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->repository->pushCriteria(new CustomDataTableCriteria($request));
        $this->repository->pushCriteria(new RequestCriteria($request));
        $collection = $this->repository->with('area')->paginate($request->get('per_page',10));

        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }

    /**
     * Store a newly created Juzgado in storage.
     * POST /juzgados
     *
     * @param CreateJuzgadoAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateJuzgadoAPIRequest $request)
    {
        $input = $request->all();

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified Juzgado.
     * GET|HEAD /juzgados/{id}
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
     * Update the specified Juzgado in storage.
     * PUT/PATCH /juzgados/{id}
     *
     * @param  int $id
     * @param UpdateJuzgadoAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateJuzgadoAPIRequest $request)
    {
        $input = $request->all();

        /** @var Juzgado $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified Juzgado from storage.
     * DELETE /juzgados/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Juzgado $model */
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
