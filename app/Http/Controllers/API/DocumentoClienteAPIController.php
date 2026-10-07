<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateDocumentoClienteAPIRequest;
use App\Http\Requests\API\UpdateDocumentoClienteAPIRequest;
use App\Models\DocumentoCliente;
use App\Repositories\DocumentoClienteRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Repositories\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class DocumentoClienteController
 * @package App\Http\Controllers\API
 */

class DocumentoClienteAPIController extends AppBaseController
{
    /** @var  DocumentoClienteRepository */
    private $documentoClienteRepository;

    public function __construct(DocumentoClienteRepository $documentoClienteRepo)
    {
        $this->documentoClienteRepository = $documentoClienteRepo;
    }

    /**
     * Display a listing of the DocumentoCliente.
     * GET|HEAD /documentoClientes
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->documentoClienteRepository->pushCriteria(new RequestCriteria($request));
        $this->documentoClienteRepository->pushCriteria(new LimitOffsetCriteria($request));
        $documentoClientes = $this->documentoClienteRepository->all();

        return $this->sendResponse($documentoClientes->toArray(), 'Documento Clientes retrieved successfully');
    }

    /**
     * Store a newly created DocumentoCliente in storage.
     * POST /documentoClientes
     *
     * @param CreateDocumentoClienteAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateDocumentoClienteAPIRequest $request)
    {
        $input = $request->all();

        $documentoClientes = $this->documentoClienteRepository->create($input);

        return $this->sendResponse($documentoClientes->toArray(), 'Documento Cliente saved successfully');
    }

    /**
     * Display the specified DocumentoCliente.
     * GET|HEAD /documentoClientes/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var DocumentoCliente $documentoCliente */
        $documentoCliente = $this->documentoClienteRepository->findWithoutFail($id);

        if (empty($documentoCliente)) {
            return $this->sendError('Documento Cliente not found');
        }

        return $this->sendResponse($documentoCliente->toArray(), 'Documento Cliente retrieved successfully');
    }

    /**
     * Update the specified DocumentoCliente in storage.
     * PUT/PATCH /documentoClientes/{id}
     *
     * @param  int $id
     * @param UpdateDocumentoClienteAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateDocumentoClienteAPIRequest $request)
    {
        $input = $request->all();

        /** @var DocumentoCliente $documentoCliente */
        $documentoCliente = $this->documentoClienteRepository->findWithoutFail($id);

        if (empty($documentoCliente)) {
            return $this->sendError('Documento Cliente not found');
        }

        $documentoCliente = $this->documentoClienteRepository->update($input, $id);

        return $this->sendResponse($documentoCliente->toArray(), 'DocumentoCliente updated successfully');
    }

    /**
     * Remove the specified DocumentoCliente from storage.
     * DELETE /documentoClientes/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var DocumentoCliente $documentoCliente */
        $documentoCliente = $this->documentoClienteRepository->findWithoutFail($id);

        if (empty($documentoCliente)) {
            return $this->sendError('Documento Cliente not found');
        }

        $documentoCliente->delete();

        return $this->sendResponse($id, 'Documento Cliente deleted successfully');
    }
}
