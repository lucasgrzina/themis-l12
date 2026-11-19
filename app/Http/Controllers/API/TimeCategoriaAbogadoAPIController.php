<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateUpdateTimeCategoriaAbogadoAPIRequest;
use App\Models\TimeCategoriaAbogado;
use App\Repositories\Criteria\CustomDataTableCriteria;
use App\Repositories\TimeCategoriaAbogadoRepository;
use Illuminate\Http\Request;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * ABM del catalogo de categorias de abogado del Time.
 * Abogados y tarifas la referencian por FK (id): renombrarla no afecta nada;
 * una categoria en uso no se puede eliminar.
 */
class TimeCategoriaAbogadoAPIController extends AppBaseController
{
    /** @var TimeCategoriaAbogadoRepository */
    private $repository;

    public function __construct(TimeCategoriaAbogadoRepository $repo)
    {
        $this->repository = $repo;
    }

    public function index(Request $request)
    {
        $this->repository->pushCriteria(new CustomDataTableCriteria($request));
        $this->repository->pushCriteria(new RequestCriteria($request));
        $collection = $this->repository->paginate($request->get('per_page', 10));

        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }

    public function store(CreateUpdateTimeCategoriaAbogadoAPIRequest $request)
    {
        $model = $this->repository->create(['nombre' => trim($request->get('nombre'))]);
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

    public function update($id, CreateUpdateTimeCategoriaAbogadoAPIRequest $request)
    {
        $model = $this->repository->findWithoutFail($id);
        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update(['nombre' => trim($request->get('nombre'))], $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    public function destroy($id)
    {
        $model = $this->repository->findWithoutFail($id);
        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }
        if (TimeCategoriaAbogado::usos($model->id) > 0) {
            return $this->sendError('La categoría está en uso por abogados o tarifas y no se puede eliminar.');
        }
        $model->delete();
        return $this->sendResponse($id, trans('api.success'));
    }

    public function removeSelected(Request $request)
    {
        $ids = $request->get('ids', []);
        foreach (TimeCategoriaAbogado::whereIn('id', $ids)->get() as $cat) {
            if (TimeCategoriaAbogado::usos($cat->id) === 0) {
                $cat->delete();
            }
        }
        if (TimeCategoriaAbogado::whereIn('id', $ids)->count() > 0) {
            return $this->sendError('Algunas categorías están en uso y no se eliminaron.');
        }
        return $this->sendResponse($ids, trans('api.success'));
    }
}
