<?php

namespace App\Http\Controllers\Traits;

use App\Models\EstadoRequerimiento;
use App\Models\TramiteAnses;
use App\Models\TramiteCliente;
use App\Repositories\RequerimientoClienteRepository;
use App\Repositories\TramiteClienteRepository;
use Illuminate\Http\Request;

trait TramitesTrait
{

    protected $currentUserId = null;

    public function storeTramite($request, $reqRepo)
    {
        $this->currentUserId = $request->user()->id;
        
        //Proceso el Tramite.
        $except = ['id','beneficios','estado','created_at','updated_at','deleted_at','rep_origen','requerimiento','usuario_archivo_id','fecha_archivo','archivar'];
        
        $datos_basicos = $request->except($except);

        try
        {
            \DB::beginTransaction();
            $model = new TramiteCliente($datos_basicos);

            $req = $reqRepo->findWithoutFail($request->get('requerimiento_id'));
            //$model->requerimiento = $req;


            if ($request->get('archivar',true) === true) 
            {
                $model = $this->archivar($model,true);
                $req->estado_req_id = $model->requerimiento->estado_req_id;
            } 
            else
            {
                $req->estado_req_id = 54;
            }


            switch ($model->area_id) {
                case 1:
                    $model = $this->processTramitePrevisional($model,$request,$reqRepo);
                    break;                
                case 2:
                    $model = $this->processTramiteLaboral($model,$request);
                    break;
                case 3:
                    $model = $this->processTramiteCivil($model,$request);
                    break;
                case 4:
                    $model = $this->processTramiteComercial($model,$request);
                    break;
                case 5:
                    $model = $this->processTramiteSocietario($model,$request);
                    # code...
                    break;
            }
            
            $model->save();
            
            
            $req->tramite_id = $model->id;
            $req->save();

            \DB::commit();
            $model->load($this->_cargarRelacionesTramites('tramite'));

        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            throw new \Exception($e->getMessage(), 1);
        }

        try 
        {
            if (env('APP_ENV','local') !== 'local') {
                \Mail::queue(new \App\Mail\TramiteIniciado($model));
            }

            if ($model->area_id == 1 &&$model->tipo_resolucion == 0 && $model->nro_beneficio) 
            {
                //a) El tipo de resolucion cambio
                //b) Es un tipo de resolucion favorable
                //c) Hay un Nro de beneficio
                //if (env('APP_ENV','local') !== 'local') {
                    //\Mail::queue(new \App\Mail\BeneficioAcordado($model));
                //}
            }             
        }
        catch (\Exception $e) {
            \Log::info($e->getMessage());
        }

        return $model;

    }

    public function updateTramite($tid, $request, $traRepo, $reqRepo)
    {
        $this->currentUserId = $request->user()->id;
        
        $model = $traRepo->findWithoutFail($tid);

        if (empty($model)) {
            throw new \Exception(trans('api.not_found'), 1);
            //return $this->sendError();
        }

        //Me fijo si ya tenia una resolucion favorable. Para no repetir mail por beneficio acordado
        $tipo_resolucion_inicial = $model->tipo_resolucion;

        //Proceso el Tramite.
        $except = ['id','beneficios','estado','created_at','updated_at','deleted_at','rep_origen','requerimiento','usuario_archivo_id','fecha_archivo','archivar'];

        $datos_basicos = $request->except($except);

        try
        {
            \DB::beginTransaction();
            
            $model->fill($datos_basicos);
            
            if ($request->get('archivar',false) === false) 
            {
                $model = $this->archivar($model,false);
            } 
            else 
            {
                if (!$model->archivar) {
                    $model = $this->archivar($model,true);
                }
            }

            switch ($model->area_id) {
                case 1:
                    $model = $this->processTramitePrevisional($model,$request,$reqRepo);
                    break;                
                case 2:
                    $model = $this->processTramiteLaboral($model,$request);
                    break;
                case 3:
                    $model = $this->processTramiteCivil($model,$request);
                    break;
                case 4:
                    $model = $this->processTramiteComercial($model,$request);
                    break;
                case 5:
                    $model = $this->processTramiteSocietario($model,$request);
                    # code...
                    break;
            }
            
            $model->save();

            /*if ($request->get('exp_judicial',null) !== null) 
            {

                $expJudicial = \Illuminate\Support\Arr::except($request->get('exp_judicial'),['juzgado','created_at','updated_at','deleted_at','id','tramite_sig']);

                if ($model->expJudicial) {
                    $model->expJudicial->update($expJudicial);
                } else {
                    $model->expJudicial()->create($expJudicial);
                }

                if (!$request->get('tramite_sig_id',false) && $request->has('exp_judicial.tramite_sig')) {

                    $tramiteSig = \Illuminate\Support\Arr::except($request->input('exp_judicial.tramite_sig'),['rep_origen','id']); 

                    if ($tramiteSig['selected']) {
                        //Tengo que replicar tramite originario
                        //Crear nuevo tramite con los datos que me llegan a tramiteSig y el id del tramite como referencia
                        //Asignar el nuevo id de tramite como tramite_sig_id
                        $nuevoTramite = $this->tratarReajusteMovilidad($model,$tramiteSig,$reqRepo);
                        //$model->tramiteSig->create($tramiteSig);
                        $model->tramite_sig_id = $nuevoTramite->id;
                        $model->archivar = true;
                        $model->save();
                    }   
                }
                
                //\Log::info($tramiteSig);
            }*/            

            \DB::commit();
            $model->load($this->_cargarRelacionesTramites('tramite'));
            
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            throw new \Exception($e->getMessage(), 1);
        }

        try 
        {
            if ($tipo_resolucion_inicial !== $model->tipo_resolucion && $model->area_id === 1 && $model->tipo_resolucion == 0 && $model->nro_beneficio) 
            {
                //a) El tipo de resolucion cambio
                //b) Es un tipo de resolucion favorable
                //c) Hay un Nro de beneficio
                //if (env('APP_ENV','local') !== 'local') {
                    //\Mail::queue(new \App\Mail\BeneficioAcordado($model));
                //}
            }
            
        }
        catch (\Exception $e) {
            //\Log::info($e->getMessage());
        }

        return $model;    
    }

    protected function processTramitePrevisional($model,$request,$reqRepo) 
    {
        //Me viene exp judicial. Si es reajuste por mobilidad y vuelta a anses, entonces genero nuevo tramite
        if ($request->get('resolucion',false)) 
        {
            if ($request->get('tipo_resolucion',1))
            {
                //No favorable => exp judicial
                if ($request->get('exp_judicial',null) !== null ) 
                {
                    if (!$model->id) 
                    {
                        $model->save();
                    }
                    $expJudicial = \Illuminate\Support\Arr::except($request->get('exp_judicial'),['juzgado','created_at','updated_at','deleted_at','id','tramite_sig']);


                    if ($model->expJudicial) {
                        $model->expJudicial->update($expJudicial);
                    } else {
                        $model->expJudicial()->create($expJudicial);
                    }


                    if ($request->get('exp_judicial')['vuelta_anses'] && !$request->get('tramite_sig_id',false) && $request->has('exp_judicial.tramite_sig')) {
                        //Es vuelta a anses y no tiene nuevo tramite generado. Genero nuevo tramite
                        
                        $tramiteSig = \Illuminate\Support\Arr::except($request->input('exp_judicial.tramite_sig'),['rep_origen','id']); 
                        $tramiteSig['rep_origen_id'] = 746; //Siempre UCADEP

                        //if ($tramiteSig['selected']) {
                            //Tengo que replicar tramite originario
                            //Crear nuevo tramite con los datos que me llegan a tramiteSig y el id del tramite como referencia
                            //Asignar el nuevo id de tramite como tramite_sig_id
                            $nuevoTramite = $this->tratarReajusteMovilidad($model,$tramiteSig,$reqRepo);
                            //$model->tramiteSig->create($tramiteSig);
                            $model->tramite_sig_id = $nuevoTramite->id;
                            $this->archivar($model,true);
                            //$model->archivar = true;
                            $model->save();
                        //}   
                    }
                    
                    //\Log::info($tramiteSig);
                } 
            } 
        }

        if ($request->get('esVueltaAnses',false))
        {
            $anses = $request->get('anses',[]);
            //\Log::info($anses);
            $deleteIds = [];
            foreach ($anses as $item) {
                if ($item['id'] != 0) 
                {
                    $deleteIds[] = $item['id']; 
                    $model->anses()->find($item['id'])->fill(\Illuminate\Support\Arr::except($item,['fecha_remision_dp','created_at','updated_at','deleted_at','estado','tramite_id']))->save();   
                } 
                else
                {
                    $modelAnses = new TramiteAnses();
                    $modelAnses->fill(\Illuminate\Support\Arr::except($item,['fecha_remision_dp','created_at','updated_at','deleted_at','estado']));
                    $modelAnses->tramite_id = $model->id;
                    $modelAnses->save();
                    $deleteIds[] = $modelAnses->id;
                }

            }
            if (count($deleteIds) > 0 ) 
            {
                $model->anses()->whereNotIn('id',$deleteIds)->delete();    
            }
            
        }
        return $model;
    }

    protected function processTramiteLaboral($model,$request) 
    {
        $model->rep_origen_id = 0;
        $tipoResolucion = $request->get('tipo_resolucion',2);

        switch ($tipoResolucion) {
            case 0:
                //Favorable, es una mediacion
                $model->resolucion = true;
                break;
            case 1:
                //No favorable => exp judicial
                $model->resolucion = true;
                if ($request->get('exp_judicial',null) !== null ) 
                {
                    if (!$model->id) 
                    {
                        $model->save();
                    }
                    $expJudicial = \Illuminate\Support\Arr::except($request->get('exp_judicial'),['juzgado','created_at','updated_at','deleted_at','id','tramite_sig']);

                    if ($model->expJudicial) {
                        $model->expJudicial->update($expJudicial);
                    } else {
                        $model->expJudicial()->create($expJudicial);
                    }
                } 
                break;
            default:
                $model->resolucion = false;
                break;
        }
        
        return $model;

        return $model;
    }

    protected function processTramiteCivil($model,$request)
    {
        $tipoResolucion = $request->get('tipo_resolucion',2);

        switch ($tipoResolucion) {
            case 0:
                //Favorable, es una mediacion
                $model->resolucion = true;
                break;
            case 1:
                //No favorable => exp judicial
                $model->resolucion = true;
                if ($request->get('exp_judicial',null) !== null ) 
                {
                    if (!$model->id) 
                    {
                        $model->save();
                    }

                    $expJudicial = \Illuminate\Support\Arr::except($request->get('exp_judicial'),['juzgado','created_at','updated_at','deleted_at','id','tramite_sig']);

                    if ($model->expJudicial) {
                        $model->expJudicial->update($expJudicial);
                    } else {
                        $model->expJudicial()->create($expJudicial);
                    }
                } 
                break;
            default:
                $model->resolucion = false;
                break;
        }
        
        return $model;
    }

    protected function processTramiteComercial($model,$request) 
    {
        $tipoResolucion = $request->get('tipo_resolucion',2);

        switch ($tipoResolucion) {
            case 0:
                //Favorable, es una mediacion
                $model->resolucion = true;
                break;
            case 1:
                //No favorable => exp judicial
                $model->resolucion = true;
                if ($request->get('exp_judicial',null) !== null ) 
                {
                    if (!$model->id) 
                    {
                        $model->save();
                    }

                    $expJudicial = \Illuminate\Support\Arr::except($request->get('exp_judicial'),['juzgado','created_at','updated_at','deleted_at','id','tramite_sig']);

                    if ($model->expJudicial) {
                        $model->expJudicial->update($expJudicial);
                    } else {
                        $model->expJudicial()->create($expJudicial);
                    }
                } 
                break;
            default:
                $model->resolucion = false;
                break;
        }
        
        return $model;
    }

    protected function processTramiteSocietario($model,$request) 
    {
        $model->rep_origen_id = 0;
        if (!$model->archivar) {
            if ($model->estado_tramite_id === 5143) {
                //CONCLUIDO
                $model = $this->archivar($model,true);
            }
        }
        return $model;
    }

    protected function archivar($model,$value=true) 
    {
        $model->archivar = $value;
        $model->usuario_archivo_id = ($value ? $this->currentUserId : null);
        $model->fecha_archivo = ($value ? \Carbon\Carbon::now() : null);

        if ($value)
        {
            $estado_archivado = EstadoRequerimiento::whereAreaId($model->area_id)->whereNombre('Concluido/Archivado')->first();

            if ($estado_archivado)
            {
                $model->requerimiento->estado_req_id = $estado_archivado->id;        
                $model->requerimiento()->update(['estado_req_id' => $estado_archivado->id]);

            }
        }
        
        return $model;
    }

    private function _cargarRelacionesTramites($grupo)
    {
        $relaciones = [
            'actuales' => [
                'tramitesActuales.repOrigen' => function($query) {
                    $query->select('id','nombre');
                },
                'tramitesActuales.estado' => function($query) {
                    $query->select('id','nombre')->withTrashed();
                },  
                'tramitesActuales.requerimiento.tipoTramite' => function($query) {
                    $query->select('id','nombre')->withTrashed();
                },                                       
                'tramitesActuales.beneficios' => function($query) {
                    $query->select('id','tramite_id','detalle','fecha_cobro');
                },  
                'tramitesActuales.requerimiento' => function($query) {
                    $query->select('id','area_id','estado_req_id','tipo_tramite_id','autos','parte');
                },
                'tramitesActuales.requerimiento.estado' => function($query) {
                    $query->select('id','nombre')->withTrashed();
                },
                'tramitesActuales.requerimiento.responsables' => function($query) {
                    $query->select('id','requerimiento_id','user_id','fecha_asignacion')->orderBy('ppal','desc')->orderBy('id','asc');
                },
                'tramitesActuales.expJudicial.juzgado',
                'tramitesActuales.tramiteSig' => function($query) {
                    $query->select('id','estado_tramite_id','expediente','fecha_inicio','tramite_ant_id','tramite_sig_id');
                },
                'tramitesActuales.tramiteAnt' => function ($query) {
                    $query->select('id','expediente','fecha_inicio','nro_beneficio');
                },
                'tramitesActuales.tramiteAnt.expJudicial' => function ($query) {
                    $query->select('id','tramite_id','nro_expediente','juzgado_id','fecha');
                },                
                'tramitesActuales.tramiteAnt.expJudicial.juzgado' => function ($query) {
                    $query->select('id','nombre');
                },                    
                'tramitesActuales.anses' => function ($query) {
                    $query->orderBy('fecha_remision');
                },                
                'tramitesActuales.anses.estado' => function ($query) {
                    $query->select('id','nombre')->withTrashed();
                },
                'tramitesActuales.cliente' => function ($query) {
                    $query->select('id','nombre_completo','nro_doc','nro_correlativo');
                }                
            ],      
            'historicos' => [
                'tramitesHistoricos.repOrigen' => function($query) {
                    $query->select('id','nombre');
                },
                'tramitesHistoricos.estado' => function($query) {
                    $query->select('id','nombre')->withTrashed();
                },                        
                'tramitesHistoricos.beneficios' => function($query) {
                    $query->select('id','tramite_id','detalle','fecha_cobro');
                },  
                'tramitesHistoricos.requerimiento' => function($query) {
                    $query->select('id','area_id','estado_req_id','tipo_tramite_id','autos','parte');
                },
                'tramitesHistoricos.requerimiento.estado' => function($query) {
                    $query->select('id','nombre')->withTrashed();
                },
                'tramitesHistoricos.requerimiento.tipoTramite' => function($query) {
                    $query->select('id','nombre')->withTrashed();
                },                               
                'tramitesHistoricos.requerimiento.responsables' => function($query) {
                    $query->select('id','requerimiento_id','user_id','fecha_asignacion')->orderBy('ppal','desc')->orderBy('id','asc');
                },
                'tramitesHistoricos.expJudicial.juzgado',
                'tramitesHistoricos.tramiteSig' => function($query) {
                    $query->select('id','estado_tramite_id','expediente','fecha_inicio','tramite_ant_id','tramite_sig_id');
                },
                'tramitesHistoricos.tramiteAnt' => function ($query) {
                    $query->select('id','expediente','fecha_inicio','nro_beneficio');
                },
                'tramitesHistoricos.tramiteAnt.expJudicial' => function ($query) {
                    $query->select('id','tramite_id','nro_expediente','juzgado_id','fecha');
                },                
                'tramitesHistoricos.tramiteAnt.expJudicial.juzgado' => function ($query) {
                    $query->select('id','nombre');
                },                    
                
                'tramitesHistoricos.anses.estado' => function ($query) {
                    $query->select('id','nombre')->withTrashed();
                },
                'tramitesHistoricos.cliente' => function ($query) {
                    $query->select('id','nombre_completo','nro_doc','nro_correlativo');
                }                
            ],                        
            'tramite' => [
                'repOrigen' => function($query) {
                    $query->select('id','nombre');
                },
                'estado' => function($query) {
                    $query->select('id','nombre')->withTrashed();
                },                        
                'beneficios' => function($query) {
                    $query->select('id','tramite_id','detalle','fecha_cobro');
                },  
                'requerimiento' => function($query) {
                    $query->select('id','area_id','estado_req_id','tipo_tramite_id','autos','parte');
                },
                'requerimiento.estado' => function($query) {
                    $query->select('id','nombre')->withTrashed();
                },
                'requerimiento.tipoTramite' => function($query) {
                    $query->select('id','nombre')->withTrashed();
                },                    
                'expJudicial.juzgado',
                'tramiteSig' => function($query) {
                    $query->select('id','estado_tramite_id','expediente','fecha_inicio','tramite_ant_id','tramite_sig_id');
                },
                'tramiteAnt' => function ($query) {
                    $query->select('id','expediente','fecha_inicio','nro_beneficio');
                },
                'tramiteAnt.expJudicial' => function ($query) {
                    $query->select('id','tramite_id','nro_expediente','juzgado_id','fecha');
                },                
                'tramiteAnt.expJudicial.juzgado' => function ($query) {
                    $query->select('id','nombre');
                },                

                'anses.estado' => function ($query) {
                    $query->select('id','nombre')->withTrashed();
                },
                'cliente' => function ($query) {
                    $query->select('id','nombre_completo','nro_doc','nro_correlativo');
                }                
            ]
        ];

        return $relaciones[$grupo];    
        
    } 

}