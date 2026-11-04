<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateTimeTipoCambioAPIRequest;
use App\Models\TimeTipoCambio;
use App\Repositories\TimeTipoCambioRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Response;

/**
 * Class TimeTipoCambioAPIController
 * @package App\Http\Controllers\API
 */
class TimeTipoCambioAPIController extends AppBaseController
{
    /** @var  TimeTipoCambioRepository */
    private $repository;

    public function __construct(TimeTipoCambioRepository $repo)
    {
        $this->repository = $repo;
    }

    public function index(Request $request)
    {
        $query = (new TimeTipoCambio())->newQuery();
        if ($request->filled('periodo')) {
            $query->where('periodo', $request->get('periodo'));
        }
        $query->orderBy('periodo','desc')->orderBy('nombre');

        $collection = $query->paginate($request->get('per_page', 50));

        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }

    public function store(CreateUpdateTimeTipoCambioAPIRequest $request)
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

    public function update($id, CreateUpdateTimeTipoCambioAPIRequest $request)
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
