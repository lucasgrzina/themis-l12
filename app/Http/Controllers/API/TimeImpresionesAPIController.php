<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Repositories\TimeHoraRepository;
use Illuminate\Http\Request;
use Response;

/**
 * Class InformesController
 * @package App\Http\Controllers\API
 */

class TimeImpresionesAPIController extends AppBaseController
{
    /** @var  InformesRepository */
    private $repository;
    private $offset = 50;

    public function __construct(TimeHoraRepository $repo)
    {
        $this->repository = $repo;
    }

    public function clienteAbogado(Request $request)
    {
        $collection = $this->repository->impresionesClienteAbogado($request,$this->offset);
        return $this->sendResponse($collection, trans('api.success'));
    }

    public function abogadoCliente(Request $request)
    {
        $collection = $this->repository->impresionesAbogadoCliente($request,$this->offset);
        return $this->sendResponse($collection, trans('api.success'));
    }

    public function abogadoResumen(Request $request)
    {
        $collection = $this->repository->impresionesAbogadoResumen($request,$this->offset);
        return $this->sendResponse($collection, trans('api.success'));
    }
}
