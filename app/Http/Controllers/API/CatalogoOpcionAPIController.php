<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateUpdateCatalogoOpcionAPIRequest;
use App\Models\CatalogoOpcion;
use App\Repositories\CatalogoOpcionRepository;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Illuminate\Http\Request;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * ABM del catalogo de opciones del wizard de alta rapida.
 * Se filtra por grupo con ?grupo=motivo_aviso (lo manda el selector de la pantalla).
 */
class CatalogoOpcionAPIController extends AppBaseController
{
    /** @var CatalogoOpcionRepository */
    private $repository;

    public function __construct(CatalogoOpcionRepository $repo)
    {
        $this->repository = $repo;
    }

    public function index(Request $request)
    {
        $grupo = $request->get('grupo');
        if ($grupo) {
            $this->repository->scopeQuery(function ($query) use ($grupo) {
                return $query->where('grupo', $grupo);
            });
        }
        $this->repository->pushCriteria(new CustomDataTableCriteria($request));
        $this->repository->pushCriteria(new RequestCriteria($request));
        $collection = $this->repository->paginate($request->get('per_page', 10));

        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }

    public function store(CreateUpdateCatalogoOpcionAPIRequest $request)
    {
        $model = $this->repository->create($request->only('grupo', 'nombre', 'orden', 'vigente'));
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

    public function update($id, CreateUpdateCatalogoOpcionAPIRequest $request)
    {
        $model = $this->repository->findWithoutFail($id);
        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }
        $model = $this->repository->update($request->only('grupo', 'nombre', 'orden', 'vigente'), $id);
        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    public function destroy($id)
    {
        $model = $this->repository->findWithoutFail($id);
        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }
        $model->delete();
        return $this->sendResponse($id, trans('api.success'));
    }

    public function removeSelected(Request $request)
    {
        $ids = $request->get('ids', []);
        $this->repository->deleteByIds($ids);
        return $this->sendResponse($ids, trans('api.success'));
    }
}
