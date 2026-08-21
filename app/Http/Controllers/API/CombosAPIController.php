<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Models\RequerimientoCliente;
use App\Repositories\AccionesControladasRepository;
use App\Repositories\AreaRepository;
use App\Repositories\ClienteRepository;
use App\Repositories\ColegaRepository;
use App\Repositories\CondIvaRepository;
use App\Repositories\DocRequeridaRepository;
use App\Repositories\EmpresaReferenciaRepository;
use App\Repositories\EstadoAnsesRepository;
use App\Repositories\EstadoRequerimientoRepository;
use App\Repositories\EstadoTramiteRepository;
use App\Repositories\JuzgadoRepository;
use App\Repositories\PaisRepository;
use App\Repositories\ProvinciaRepository;
use App\Repositories\ReparticionOrigenRepository;
use App\Repositories\RolesRepository;
use App\Repositories\TimeGestionRepository;
use App\Repositories\TimeHoraRepository;
use App\Repositories\TimeReferenciaRepository;
use App\Repositories\TipoAporteRepository;
use App\Repositories\TipoDocumentoRepository;
use App\Repositories\TipoSociedadRepository;
use App\Repositories\TipoTramiteRepository;
use App\Repositories\UsersRepository;
use Illuminate\Http\Request;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;


class CombosAPIController extends AppBaseController
{
    /** @var  RolesRepository */
    private $repository;
    public function timeClientes($con_horas = false, ClienteRepository $repo,Request $request)
    {
        $repo->pushCriteria(new RequestCriteria($request));
        $data = $repo->orderBy('nombre_completo')->scopeQuery(function($q) use ($con_horas) {
            if ($con_horas) {
                $q = $q->whereHas('horas');
            }
            return $q;
        })->all(['id','nombre_completo']);
        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }
    public function roles(RolesRepository $repo)
    {
        //$repo->pushCriteria(new RequestCriteria($request));
        $data = $repo->orderBy('name')->all(['id','name']);
        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }
    
    public function acciones(AccionesControladasRepository $repo)
    {
        //$repo->pushCriteria(new RequestCriteria($request));
        $data = $repo->orderBy('nombre')->all(['nombre_permiso','nombre']);
        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }

    public function condIva(CondIvaRepository $repo)
    {
        //$repo->pushCriteria(new RequestCriteria($request));
        $data = $repo->orderBy('nombre')->all(['id','nombre']);
        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }
    public function areas(AreaRepository $repo)
    {
        //$repo->pushCriteria(new RequestCriteria($request));
        $data = $repo->orderBy('nombre')->all(['id','nombre']);
        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }
    public function docRequerida(DocRequeridaRepository $repo,Request $request)
    {
        $repo->pushCriteria(new RequestCriteria($request));
        $data = $repo->orderBy('nombre')->all(['id','nombre']);
        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }
    public function docRequeridaTT($id,TipoTramiteRepository $repo)
    {
        $data = [];

        $model = $repo->with('doc_requerida.doc')->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }   

        $data = $model->doc_requerida;         

        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }
    public function docRequeridaTA($id,TipoAporteRepository $repo)
    {
        $data = [];

        $model = $repo->with('doc_requerida.doc')->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }   

        $data = $model->doc_requerida;         

        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }

    public function colegas(ColegaRepository $repo,Request $request)
    {
        $repo->pushCriteria(new RequestCriteria($request));
        $data = $repo->scopeQuery(function($query){
            return $query->whereVigente(true)->orderBy('nombre','asc'); 
        })->all(['id','nombre']);
        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }
    public function tipoTramites(TipoTramiteRepository $repo,Request $request)
    {
        $repo->pushCriteria(new RequestCriteria($request));
        $data = $repo->scopeQuery(function($query){
            return $query->whereVigente(true)->orderBy('nombre','asc'); 
        })->all(['id','nombre']);
        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }
    public function responsables($areaId,UsersRepository $repo,Request $request)
    {
        $repo->pushCriteria(new RequestCriteria($request));
        $data =  $repo->scopeQuery(function($query) use($areaId){
            return $query->whereVisible(true)->whereResponsable(true)->whereHas('areas', function($query) use($areaId){
                $query->whereAreaId($areaId);
            })->orderBy('name','asc'); 
        })->all(['id','name']);        
        /*$data = $repo->scopeQuery(function($query){
            return $query->whereResponsable(true)->orderBy('name','asc'); 
        })->all(['id','name']);*/
        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }        
    public function tipoDoc(TipoDocumentoRepository $repo)
    {
        //$repo->pushCriteria(new RequestCriteria($request));
        $data = $repo->orderBy('nombre')->all(['id','nombre']);
        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }

    public function paises(PaisRepository $repo)
    {
        //$repo->pushCriteria(new RequestCriteria($request));
        $data = $repo->orderBy('nombre')->all(['id','nombre']);
        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }

    public function provincias(ProvinciaRepository $repo)
    {
        //$repo->pushCriteria(new RequestCriteria($request));
        $data = $repo->orderBy('nombre')->all(['id','nombre']);
        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }

    public function amCliente(PaisRepository $paisRepo,ProvinciaRepository $provRepo,TipoDocumentoRepository $tipoDocRepo, EmpresaReferenciaRepository $empRepo,TipoAporteRepository $tipoAporteRepo,TipoSociedadRepository $tipoSocRepo, CondIvaRepository $condIvaRepo)
    {
        $data = [ 
            'tipo_doc' => $tipoDocRepo->orderBy('nombre')->all(['id','nombre']),
            'paises' => $paisRepo->orderBy('nombre')->all(['id','nombre'])->toArray(),
            'provincias' => $provRepo->orderBy('nombre')->all(['id','nombre'])->toArray(),
            'nacionalidad' => [['value' => 'A','name' => 'Argentino'],['value' => 'E','name' => 'Extranjero'],['value' => 'N','name' => 'Naturalizado']],
            'estado_civil' => [['value' => '','name' => ''],['value' => 'SO','name' => 'Soltero'],['value' => 'CA','name' => 'Casado'],['value' => 'VI','name' => 'Viudo'],['value' => 'CO','name' => 'Conviviente'],['value' => 'DI', 'name' => 'Divorciado']],
            'sexo' => [['value' => '','name' => ''],['value' => 'M','name' => 'Masculino'],['value' => 'F','name' => 'Femenino']],
            'categorias' => [['id' => 'P', 'nombre'=> 'Particular'],['id' => 'E', 'nombre' => 'Empresa']],
            'empresas_referencia' => array_merge([['id' => '', 'razon_social' => '']],$empRepo->orderBy('razon_social')->all(['id','razon_social'])->toArray()),
            'tipos_aporte' => array_merge([['id' => '', 'nombre' => '']],$tipoAporteRepo->orderBy('nombre')->all(['id','nombre'])->toArray()),
            'tipos_soc' => array_merge([['id' => '', 'nombre' => '']],$tipoSocRepo->orderBy('nombre')->all(['id','nombre'])->toArray()),
            'cond_iva' => array_merge([['id' => '', 'nombre' => '']],$condIvaRepo->orderBy('nombre')->all(['id','nombre'])->toArray()),    
            'libros_estudio' => [['value' => '','name' => ''],['value' => true,'name' => 'Si'],['value' => false,'name' => 'No']],        
        ];

        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }
    
    public function solapaRequerimientos($areaId,TipoDocumentoRepository $tipoDocRepo, TipoTramiteRepository $tipoTramitesRepo, EstadoRequerimientoRepository $estadoReqRepo, ReparticionOrigenRepository $repOrigenRepo, TipoAporteRepository $tipoAporteRepo,UsersRepository $userRepo)
    {
        $data = [ 
            'tipo_doc' => $tipoDocRepo->orderBy('nombre')->all(['id','nombre']),
            'responsables' => [
                'selected' => null,
                'data' => $userRepo->scopeQuery(function($query) use($areaId){
                    return $query->whereVisible(true)->whereResponsable(true)->whereHas('areas', function($query) use($areaId){
                        $query->whereAreaId($areaId);
                    })->orderBy('name','asc'); 
                })->all(['id','name'])
            ],              
            'estado_civil' => [['value' => '','name' => ''],['value' => 'CA','name' => 'Casado'],['value' => 'CO','name' => 'Conviviente']],
            'tipo_tramites' => $tipoTramitesRepo->scopeQuery(function($query) use($areaId){
                return $query->whereAreaId($areaId)->whereVigente(true)->orderBy('nombre','asc'); 
            })->all(['id','nombre','tratamiento','inicia_tramite']),
            'estado_req' => $estadoReqRepo->scopeQuery(function($query) use($areaId){
                return $query->whereAreaId($areaId)->whereVigente(true)->orderBy('nombre','asc'); 
            })->all(['id','nombre']),
            'colegas' => [],
            'hijos' => [['value' => '','name' => ''],['value' => false,'name' => 'NO'],['value' => true,'name' => 'SI']],
            'rep_origen' => $repOrigenRepo->scopeQuery(function($query) use($areaId){
                return $query->whereAreaId($areaId)->whereVigente(true)->orderBy('nombre','asc'); 
            })->all(['id','nombre']),
            'tipos_aporte' => $tipoAporteRepo->orderBy('nombre')->all(['id','nombre'])->toArray(),
            'partes' => RequerimientoCliente::getComboPartes()           
        ];

        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }  

    public function solapaTramites($areaId, EstadoTramiteRepository $estadoTraRepo, ReparticionOrigenRepository $repOrigenRepo, EstadoAnsesRepository $estadoAnsesRepo)
    {
        $data = [ 
            'estado_tra' => $estadoTraRepo->scopeQuery(function($query) use($areaId){
                return $query->whereAreaId($areaId)->whereVigente(true)->orderBy('nombre','asc'); 
            })->all(['id','nombre']),
            'rep_origen' => $repOrigenRepo->scopeQuery(function($query) use($areaId){
                return $query->whereAreaId($areaId)->whereVigente(true)->orderBy('nombre','asc'); 
            })->all(['id','nombre']),

        ];

        if ($areaId == 1) {
            $data['estado_anses'] = $estadoAnsesRepo->scopeQuery(function($query) use($areaId){
                return $query->whereVigente(true)->orderBy('nombre','asc'); 
            })->all(['id','nombre']);
        }
        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }       

    public function solapaAvisos(UsersRepository $userRepo, AreaRepository $areasRepo)
    {
        $data = [ 
            'areas' => $areasRepo->orderBy('nombre')->all(['id','nombre']),
            'usuarios' => $userRepo->scopeQuery(function($query){
                return $query->orderBy('name','asc'); 
            })->all(['id','name','visible'])
        ];

        return $this->sendResponse($data, 'La operacion finalizo con exito');
    } 


    public function expJudicial($areaId,JuzgadoRepository $juzgadoRepo)
    {
        $data = [ 
            //'areas' => $request->user()->getAreasPorUsuario($request->user()->id),
            'juzgados' => $juzgadoRepo->scopeQuery(function($query) use($areaId){
                return $query->whereAreaId($areaId)->whereVigente(true)->orderBy('nombre','asc'); 
            })->orderBy('nombre')->all(['id','nombre']),
        ];

        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }    

    public function infBeneficios($areaId, Request $request,AreaRepository $areasRepo,TipoTramiteRepository $tipoTramitesRepo, EstadoTramiteRepository $estadoTraRepo, ReparticionOrigenRepository $repOrigenRepo, UsersRepository $userRepo)
    {
        $responsables = [];
        $areas = $request->user()->getAreasPorUsuario($request->user()->id);
        $responsableArea = false;
        
        foreach ($areas as $area) {
            if ($area['id'] == $areaId && $area['responsable']) {
                $responsableArea = true;
            }
        }

        if ($responsableArea) {
            $responsables = $userRepo->scopeQuery(function($query) use($areaId){
                return $query->whereVisible(true)->whereResponsable(true)->whereHas('areas', function($query) use($areaId){
                    $query->whereAreaId($areaId);
                })->orderBy('name','asc'); 
            })->all(['id','name']);
        } else {
            $responsables[] = [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'responsable' => false
            ];
        }


        $data = [
            'areas' => $areas,
            'responsables' => $responsables,                
            'tipo_tramites' => $tipoTramitesRepo->scopeQuery(function($query) use($areaId){
                return $query->whereAreaId($areaId)->whereVigente(true)->orderBy('nombre','asc'); 
            })->all(['id','nombre','tratamiento']),
            'estado_tra' => $estadoTraRepo->scopeQuery(function($query) use($areaId){
                return $query->whereAreaId($areaId)->whereVigente(true)->orderBy('nombre','asc'); 
            })->all(['id','nombre']),
            'rep_origen' => $repOrigenRepo->scopeQuery(function($query) use($areaId){
                return $query->whereVigente(true)->orderBy('nombre','asc'); 
            })->all(['id','nombre']),
        ];

        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }    

    public function infReqEmpresas($areaId,Request $request, AreaRepository $areasRepo,TipoTramiteRepository $tipoTramitesRepo, EstadoRequerimientoRepository $estadoReqRepo, EmpresaReferenciaRepository $empRefRepo, UsersRepository $userRepo,ColegaRepository $colegasRepo)
    {
        $responsables = [];
        $areas = $request->user()->getAreasPorUsuario($request->user()->id);
        $responsableArea = false;
        
        foreach ($areas as $area) {
            if ($area['id'] == $areaId && $area['responsable']) {
                $responsableArea = true;
            }
        }

        if ($responsableArea) {
            $responsables = $userRepo->scopeQuery(function($query) use($areaId){
                return $query->whereVisible(true)->whereResponsable(true)->whereHas('areas', function($query) use($areaId){
                    $query->whereAreaId($areaId);
                })->orderBy('name','asc'); 
            })->all(['id','name']);
        } else {
            $responsables[] = [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'responsable' => false
            ];
        }        
        $data = [
            'areas' => $areas,
            'responsables' => $responsables,               
            'tipo_tramites' => $tipoTramitesRepo->scopeQuery(function($query) use($areaId){
                return $query->whereAreaId($areaId)->whereVigente(true)->orderBy('nombre','asc'); 
            })->all(['id','nombre','tratamiento']),
            'estado_req' => $estadoReqRepo->scopeQuery(function($query) use($areaId){
                return $query->whereAreaId($areaId)->whereVigente(true)->orderBy('nombre','asc'); 
            })->all(['id','nombre']),
            'empresas_referencia' => $empRefRepo->scopeQuery(function($query) use($areaId){
                return $query->orderBy('razon_social','asc'); 
            })->all(['id','razon_social']),
            'colegas' => $colegasRepo->scopeQuery(function($query) use($areaId){
                return $query->whereVigente(true)->orderBy('nombre','asc'); 
            })->all(['id','nombre']),            
        ];

        return $this->sendResponse($data, 'La operacion finalizo con exito');
    
    }  

   public function infUcadep($areaId, Request $request,AreaRepository $areasRepo,EstadoAnsesRepository $estadoAnsesRepo, UsersRepository $userRepo)
    {
        $responsables = [];
        $areas = $request->user()->getAreasPorUsuario($request->user()->id);
        $responsableArea = false;
        
        foreach ($areas as $area) {
            if ($area['id'] == $areaId && $area['responsable']) {
                $responsableArea = true;
            }
        }

        if ($responsableArea) {
            $responsables = $userRepo->scopeQuery(function($query) use($areaId){
                return $query->whereVisible(true)->whereResponsable(true)->whereHas('areas', function($query) use($areaId){
                    $query->whereAreaId($areaId);
                })->orderBy('name','asc'); 
            })->all(['id','name']);
        } else {
            $responsables[] = [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'responsable' => false
            ];
        }


        $data = [
            'areas' => $areas,
            'responsables' => $responsables,                
            'estados' => $estadoAnsesRepo->scopeQuery(function($query) use($areaId){
                return $query->whereVigente(true)->orderBy('nombre','asc'); 
            })->all(['id','nombre']),
        ];

        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }   

    public function usuarios(AreaRepository $areasRepo,RolesRepository $rolesRepo)
    {
        $data = [ 
            'areas' => $areasRepo->orderBy('nombre')->all(['id','nombre']),
            'roles' => $rolesRepo->orderBy('name')->all(['id','name']),
        ];

        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }  

    public function timeReferencias($clienteId,TimeReferenciaRepository $refRepo)
    {
        $data = [ 
            'referencias' => $refRepo->scopeQuery(function($q) use($clienteId) {
                return $q->whereClienteId($clienteId)->orderBy('nombre','asc'); 
            })->all(['id','nombre'])
        ];

        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }

    public function timeAMHoras(TimeGestionRepository $gestionRepo)
    {
        $data = [ 
            'gestion' => $gestionRepo->scopeQuery(function($q) {
                return $q->whereVigente(true)->orderBy('nombre','asc'); 
            })->all(['id','nombre'])
        ];

        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }     

    public function timeAbogados(UsersRepository $userRepo)
    {
        $data =  $userRepo->scopeQuery(function($query){
            return $query
                        ->whereVisible(true)
                        ->whereResponsable(true)
                        ->whereIn('time_level',[1,5])
                        ->orderBy('name','asc'); 
        })->all(['id','name']);        


        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }         

}