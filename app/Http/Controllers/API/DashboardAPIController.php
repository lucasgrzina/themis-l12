<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Models\AvisoCliente;
use App\Models\Cliente;
use App\Repositories\ClienteRepository;
use App\Repositories\UsersRepository;
use App\User;
use Illuminate\Http\Request;
use Response;


class DashboardAPIController extends AppBaseController
{

    public function index(Request $request)
    {
        //$repo->pushCriteria(new RequestCriteria($request));
        $data = [
            'usuarios' => User::whereVisible(true)->count(),
            'clientesJ' => Cliente::wherePersoneria('J')->count(),
            'clientesH' => Cliente::wherePersoneria('H')->count(),
            'avisos' => $this->avisosPendientes($request)

        ];


        return $this->sendResponse($data, 'La operacion finalizo con exito');
    }
    
    private function avisosPendientes($request) 
    {
        $user_id = $request->user()->id;
        $area_ids = $request->user()->areas->pluck('area_id');

        $desde = \Carbon\Carbon::today()->subDays(config('themis.avisos.dias_vencidos',0))->format('Y-m-d 00:00:00');
        $hasta = \Carbon\Carbon::today()->addDays(config('themis.avisos.dias_previos',0))->format('Y-m-d 23:59:59');
            return AvisoCliente::whereDescartado(false)
                        ->where(function ($query) use($user_id,$area_ids) {
                            $query->where(function ($query) use($user_id) {
                                $query->whereType('U')->whereTypeId($user_id);
                            })->orWhere(function ($query) use($area_ids){
                                $query->whereType('A')->whereIn('type_id',$area_ids);
                            });
                        })
                        ->where('fecha','<=',$hasta)->where('fecha','>=',$desde)
                        ->count();
    }
}