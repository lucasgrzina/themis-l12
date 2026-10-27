<?php

namespace App\Http\Controllers\API;

use App\Repositories\InformesRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Response;

/**
 * Class InformesController
 * @package App\Http\Controllers\API
 */

class InformesAPIController extends AppBaseController
{
    /** @var  InformesRepository */
    private $repository;
    private $offset = 50;
    public function __construct(InformesRepository $repo)
    {
        $this->repository = $repo;
    }

    public function expedientesJudiciales(Request $request)
    {
        $collection = $this->repository->expedientesJudiciales($request,$this->offset);
        //$collection = [];
        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }

    public function beneficios(Request $request)
    {
        $collection = $this->repository->beneficios($request,$this->offset);
        //$collection = [];
        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }

    public function tramites(Request $request)
    {
        $collection = $this->repository->tramites($request,$this->offset);
        //$collection = [];
        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }    

    public function pensiones(Request $request)
    {
        $collection = $this->repository->pensiones($request,$this->offset);
        //$collection = [];
        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }     

    public function tramitesHistoricos(Request $request)
    {
        $collection = $this->repository->tramitesHistoricos($request,$this->offset);
        //$collection = [];
        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }

    /**
     * Marca MANUALMENTE la carpeta fisica de un tramite archivado como destruida.
     * Accion definitiva (no reversible desde el listado), gateada por el permiso
     * 'archivar-tramites:D'. Registra quien y cuando.
     */
    public function destruirCarpeta($id, Request $request)
    {
        if (! $request->user()->can('tramites:DC')) {
            return $this->sendError('No tiene permiso para marcar carpetas como destruidas.', 403);
        }

        $tramite = \App\Models\TramiteCliente::find($id);
        if (empty($tramite)) {
            return $this->sendError(trans('api.not_found'), 404);
        }
        if (! $tramite->archivar) {
            return $this->sendError('Solo se puede destruir la carpeta de un tramite archivado.', 422);
        }
        if ($tramite->carpeta_destruida) {
            return $this->sendError('La carpeta ya figura como destruida.', 422);
        }

        $fecha = \Carbon\Carbon::now()->toDateString();
        $tramite->carpeta_destruida = true;
        $tramite->fecha_destruccion = $fecha;
        $tramite->usuario_destruccion_id = $request->user()->id;
        $tramite->save();

        return $this->sendResponse([
            'tramite_id'          => $tramite->id,
            'carpeta_destruida'   => true,
            'fecha_destruccion'   => $fecha,
            'usuario_destruccion' => $request->user()->name,
        ], 'Carpeta marcada como destruida.');
    }
    
    public function requerimientosEmpresas(Request $request)
    {
        $collection = $this->repository->requerimientosEmpresas($request,$this->offset);
        //$collection = [];
        return $this->sendResponse($collection, trans('api.success'));
    }      
    
    public function ucadep(Request $request)
    {
        $collection = $this->repository->ucadep($request,$this->offset);
        //$collection = [];
        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }    
}
