<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateObservacionClienteAPIRequest;
use App\Http\Requests\API\UpdateObservacionClienteAPIRequest;
use App\Models\ObservacionCliente;
use App\Repositories\ObservacionClienteRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Repositories\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class ObservacionClienteController
 * @package App\Http\Controllers\API
 */

class ObservacionClienteAPIController extends AppBaseController
{
    /** @var  ObservacionClienteRepository */
    private $observacionClienteRepository;

    public function __construct(ObservacionClienteRepository $observacionClienteRepo)
    {
        $this->observacionClienteRepository = $observacionClienteRepo;
    }

    /**
     * Display a listing of the ObservacionCliente.
     * GET|HEAD /observacionClientes
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->observacionClienteRepository->pushCriteria(new RequestCriteria($request));
        $this->observacionClienteRepository->pushCriteria(new LimitOffsetCriteria($request));
        $observacionClientes = $this->observacionClienteRepository->all();

        return $this->sendResponse($observacionClientes->toArray(), 'Observacion Clientes retrieved successfully');
    }

    /**
     * Store a newly created ObservacionCliente in storage.
     * POST /observacionClientes
     *
     * @param CreateObservacionClienteAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateObservacionClienteAPIRequest $request)
    {
        $input = $request->all();

        $observacionClientes = $this->observacionClienteRepository->create($input);

        return $this->sendResponse($observacionClientes->toArray(), 'Observacion Cliente saved successfully');
    }

    /**
     * Display the specified ObservacionCliente.
     * GET|HEAD /observacionClientes/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var ObservacionCliente $observacionCliente */
        $observacionCliente = $this->observacionClienteRepository->findWithoutFail($id);

        if (empty($observacionCliente)) {
            return $this->sendError('Observacion Cliente not found');
        }

        return $this->sendResponse($observacionCliente->toArray(), 'Observacion Cliente retrieved successfully');
    }

    /**
     * Update the specified ObservacionCliente in storage.
     * PUT/PATCH /observacionClientes/{id}
     *
     * @param  int $id
     * @param UpdateObservacionClienteAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateObservacionClienteAPIRequest $request)
    {
        $input = $request->all();

        /** @var ObservacionCliente $observacionCliente */
        $observacionCliente = $this->observacionClienteRepository->findWithoutFail($id);

        if (empty($observacionCliente)) {
            return $this->sendError('Observacion Cliente not found');
        }

        $observacionCliente = $this->observacionClienteRepository->update($input, $id);

        return $this->sendResponse($observacionCliente->toArray(), 'ObservacionCliente updated successfully');
    }

    /**
     * Remove the specified ObservacionCliente from storage.
     * DELETE /observacionClientes/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var ObservacionCliente $observacionCliente */
        $observacionCliente = $this->observacionClienteRepository->findWithoutFail($id);

        if (empty($observacionCliente)) {
            return $this->sendError('Observacion Cliente not found');
        }

        $observacionCliente->delete();

        return $this->sendResponse($id, 'Observacion Cliente deleted successfully');
    }
}
