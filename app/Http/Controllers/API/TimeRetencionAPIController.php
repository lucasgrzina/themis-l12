<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateTimeRetencionAPIRequest;
use App\Models\TimeRetencion;
use App\Repositories\TimeRetencionRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Response;

/**
 * Class TimeRetencionAPIController
 * @package App\Http\Controllers\API
 */
class TimeRetencionAPIController extends AppBaseController
{
    /** @var  TimeRetencionRepository */
    private $repository;

    public function __construct(TimeRetencionRepository $repo)
    {
        $this->repository = $repo;
    }

    public function index(Request $request)
    {
        $query = (new TimeRetencion())->newQuery();
        $query->orderBy('codigo')->orderBy('vigencia_desde','desc');

        $collection = $query->paginate($request->get('per_page', 50));

        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }

    public function store(CreateUpdateTimeRetencionAPIRequest $request)
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

    public function update($id, CreateUpdateTimeRetencionAPIRequest $request)
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
