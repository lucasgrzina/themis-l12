<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateAccionesControladasAPIRequest;
use App\Http\Requests\API\UpdateAccionesControladasAPIRequest;
use App\Models\AccionesControladas;
use App\Repositories\AccionesControladasRepository;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Illuminate\Http\Request;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class AccionesControladasController
 * @package App\Http\Controllers\API
 */

class AccionesControladasAPIController extends AppBaseController
{
    /** @var  AccionesControladasRepository */
    private $accionesControladasRepository;

    public function __construct(AccionesControladasRepository $accionesControladasRepo)
    {
        $this->accionesControladasRepository = $accionesControladasRepo;
    }

    /**
     * Display a listing of the AccionesControladas.
     * GET|HEAD /accionesControladas
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {

        $this->accionesControladasRepository->pushCriteria(new CustomDataTableCriteria($request));
        $this->accionesControladasRepository->pushCriteria(new RequestCriteria($request));
        
        $accionesControladas = $this->accionesControladasRepository->paginate($request->get('per_page',10));

        return $this->sendResponse($accionesControladas->toArray(), 'Acciones Controladas retrieved successfully');
    }

    /**
     * Store a newly created AccionesControladas in storage.
     * POST /accionesControladas
     *
     * @param CreateAccionesControladasAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateAccionesControladasAPIRequest $request)
    {
        $input = $request->all();

        $accionesControladas = $this->accionesControladasRepository->create($input);

        return $this->sendResponse($accionesControladas->toArray(), 'Acciones Controladas saved successfully');
    }

    /**
     * Display the specified AccionesControladas.
     * GET|HEAD /accionesControladas/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var AccionesControladas $accionesControladas */
        $accionesControladas = $this->accionesControladasRepository->findWithoutFail($id);

        if (empty($accionesControladas)) {
            return $this->sendError('Acciones Controladas not found');
        }

        return $this->sendResponse($accionesControladas->toArray(), 'Acciones Controladas retrieved successfully');
    }

    /**
     * Update the specified AccionesControladas in storage.
     * PUT/PATCH /accionesControladas/{id}
     *
     * @param  int $id
     * @param UpdateAccionesControladasAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateAccionesControladasAPIRequest $request)
    {
        $input = $request->all();

        /** @var AccionesControladas $accionesControladas */
        $accionesControladas = $this->accionesControladasRepository->findWithoutFail($id);

        if (empty($accionesControladas)) {
            return $this->sendError('Acciones Controladas not found');
        }

        $accionesControladas = $this->accionesControladasRepository->update($input, $id);

        return $this->sendResponse($accionesControladas->toArray(), 'AccionesControladas updated successfully');
    }

    /**
     * Remove the specified AccionesControladas from storage.
     * DELETE /accionesControladas/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var AccionesControladas $accionesControladas */
        $accionesControladas = $this->accionesControladasRepository->findWithoutFail($id);

        if (empty($accionesControladas)) {
            return $this->sendError('Acciones Controladas not found');
        }

        $accionesControladas->delete();

        return $this->sendResponse($id, 'Acciones Controladas deleted successfully');
    }
}
