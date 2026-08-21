<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateTipoDocumentoAPIRequest;
use App\Http\Requests\API\UpdateTipoDocumentoAPIRequest;
use App\Models\TipoDocumento;
use App\Repositories\Criteria\CustomDataTableCriteria;
use App\Repositories\TipoDocumentoRepository;
use Illuminate\Http\Request;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class TipoDocumentoController
 * @package App\Http\Controllers\API
 */

class TipoDocumentoAPIController extends AppBaseController
{
    /** @var  TipoDocumentoRepository */
    private $tipoDocumentoRepository;

    public function __construct(TipoDocumentoRepository $tipoDocumentoRepo)
    {
        $this->tipoDocumentoRepository = $tipoDocumentoRepo;
    }

    /**
     * Display a listing of the TipoDocumento.
     * GET|HEAD /tipoDocumentos
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->tipoDocumentoRepository->pushCriteria(new CustomDataTableCriteria($request));
        $this->tipoDocumentoRepository->pushCriteria(new RequestCriteria($request));
        
        $tipoDocumentos = $this->tipoDocumentoRepository->paginate($request->get('per_page',10));

        return $this->sendResponse($tipoDocumentos->toArray(), trans('api.success'));
    }

    /**
     * Store a newly created TipoDocumento in storage.
     * POST /tipoDocumentos
     *
     * @param CreateTipoDocumentoAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateTipoDocumentoAPIRequest $request)
    {
        $input = $request->all();

        $tipoDocumentos = $this->tipoDocumentoRepository->create($input);

        return $this->sendResponse($tipoDocumentos->toArray(), trans('api.success'));
    }

    /**
     * Display the specified TipoDocumento.
     * GET|HEAD /tipoDocumentos/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var TipoDocumento $tipoDocumento */
        $tipoDocumento = $this->tipoDocumentoRepository->findWithoutFail($id);

        if (empty($tipoDocumento)) {
            return $this->sendError(trans('api.not_found'));
        }

        return $this->sendResponse($tipoDocumento->toArray(), trans('api.success'));
    }

    /**
     * Update the specified TipoDocumento in storage.
     * PUT/PATCH /tipoDocumentos/{id}
     *
     * @param  int $id
     * @param UpdateTipoDocumentoAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateTipoDocumentoAPIRequest $request)
    {
        $input = $request->all();

        /** @var TipoDocumento $tipoDocumento */
        $tipoDocumento = $this->tipoDocumentoRepository->findWithoutFail($id);

        if (empty($tipoDocumento)) {
            return $this->sendError(trans('api.not_found'));
        }

        $tipoDocumento = $this->tipoDocumentoRepository->update($input, $id);

        return $this->sendResponse($tipoDocumento->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified TipoDocumento from storage.
     * DELETE /tipoDocumentos/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var TipoDocumento $tipoDocumento */
        $tipoDocumento = $this->tipoDocumentoRepository->findWithoutFail($id);

        if (empty($tipoDocumento)) {
            return $this->sendError(trans('api.not_found'));
        }

        $tipoDocumento->delete();

        return $this->sendResponse($id, trans('api.success'));
    }

    public function removeSelected(Request $request)
    {
        $ids = $request->get('ids',[]);
        $this->tipoDocumentoRepository->deleteByIds($ids);
        return $this->sendResponse($ids, trans('api.success'));   
    }
    
}
