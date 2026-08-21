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
