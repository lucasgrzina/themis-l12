<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateTimeClienteParametroAPIRequest;
use App\Models\TimeClienteParametro;
use App\Repositories\TimeClienteParametroRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Response;

/**
 * Class TimeClienteParametroAPIController
 * @package App\Http\Controllers\API
 */
class TimeClienteParametroAPIController extends AppBaseController
{
    /** @var  TimeClienteParametroRepository */
    private $repository;

    public function __construct(TimeClienteParametroRepository $repo)
    {
        $this->repository = $repo;
    }

    public function index(Request $request)
    {
        $query = (new TimeClienteParametro())->newQuery();
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->get('cliente_id'));
        }
        $query->orderBy('cliente_id')->orderByRaw('vigencia_desde is null desc')->orderByDesc('vigencia_desde');

        $collection = $query->paginate($request->get('per_page', 50));

        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }

    public function store(CreateUpdateTimeClienteParametroAPIRequest $request)
    {
        $model = $this->repository->create($request->all());
        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    public function show($id)
    {
        $model = $this->repository->findWithoutFail($id);
        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }
        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    public function update($id, CreateUpdateTimeClienteParametroAPIRequest $request)
    {
        $model = $this->repository->findWithoutFail($id);
        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }
        $model = $this->repository->update($request->all(), $id);
        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    public function destroy($id)
    {
        $model = $this->repository->findWithoutFail($id);
        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }
        try {
            $model->delete();
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500);
        }
        return $this->sendResponse($id, trans('api.success'));
    }

    public function removeSelected(Request $request)
    {
        $ids = $request->get('ids', []);
        $this->repository->deleteByIds($ids);
        return $this->sendResponse($ids, trans('api.success'));
    }
}
