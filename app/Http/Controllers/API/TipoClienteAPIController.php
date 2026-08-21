<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateTipoClienteAPIRequest;
use App\Models\TipoCliente;
use App\Repositories\TipoClienteRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class TipoClienteController
 * @package App\Http\Controllers\API
 */

class TipoClienteAPIController extends AppBaseController
{
    /** @var  TipoClienteRepository */
    private $repository;

    public function __construct(TipoClienteRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the TipoCliente.
     * GET|HEAD /tipoClientes
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->repository->pushCriteria(new CustomDataTableCriteria($request));
        $this->repository->pushCriteria(new RequestCriteria($request));
        
        //$collection = $this->repository->paginate($request->get('per_page',10));
        $collection = $this->repository->with([
            'doc_requerida' => function($query) {
                $query->select('id','doc_requerida_id','tipo_cliente_id');
            },
            'doc_requerida.doc' => function($query) {
                $query->select('id','nombre','descripcion');
            }
        ])->paginate($request->get('per_page',10));

        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }

    /**
     * Store a newly created TipoCliente in storage.
     * POST /tipoClientes
     *
     * @param CreateTipoClienteAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateTipoClienteAPIRequest $request)
    {
        $input = $request->except('doc_requerida');

        $model = $this->repository->create($input);

        $docRequerida = $request->get('doc_requerida',[]);
        
        $model->doc_requerida()->delete();
        $model->doc_requerida()->createMany($docRequerida);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified TipoCliente.
     * GET|HEAD /tipoClientes/{id}
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
     * Update the specified TipoCliente in storage.
     * PUT/PATCH /tipoClientes/{id}
     *
     * @param  int $id
     * @param UpdateTipoClienteAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateTipoClienteAPIRequest $request)
    {
        $input = $request->except('doc_requerida');

        /** @var TipoTramite $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        $docRequerida = $request->get('doc_requerida',[]);
        
        $model->doc_requerida()->delete();
        $model->doc_requerida()->createMany($docRequerida);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified TipoCliente from storage.
     * DELETE /tipoClientes/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var TipoCliente $model */
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
