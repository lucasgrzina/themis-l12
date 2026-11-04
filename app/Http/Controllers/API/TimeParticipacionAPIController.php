<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateTimeParticipacionAPIRequest;
use App\Models\TimeParticipacion;
use App\Repositories\TimeParticipacionRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Response;

/**
 * Class TimeParticipacionAPIController
 * @package App\Http\Controllers\API
 */
class TimeParticipacionAPIController extends AppBaseController
{
    /** @var  TimeParticipacionRepository */
    private $repository;

    public function __construct(TimeParticipacionRepository $repo)
    {
        $this->repository = $repo;
    }

    public function index(Request $request)
    {
        $query = (new TimeParticipacion())->newQuery()->with(['cliente' => function($q){ $q->select('id','nombre_completo'); }, 'usuario' => function($q){ $q->select('id','name'); }]);
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->get('cliente_id'));
        }
        if ($request->filled('periodo')) {
            $query->where('periodo', $request->get('periodo'));
        }
        $query->orderBy('cliente_id')->orderBy('user_id')->orderByRaw('periodo is null desc')->orderBy('periodo');

        $collection = $query->paginate($request->get('per_page', 50));

        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }

    public function store(CreateUpdateTimeParticipacionAPIRequest $request)
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

    public function update($id, CreateUpdateTimeParticipacionAPIRequest $request)
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
