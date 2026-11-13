<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Repositories\TimeHoraRepository;
use App\Services\TimePermisos;
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

    const TIPOS_FIRMADOS = [
        'imp-abogado-resumen' => 'imprimir.time.imp-abogado-resumen',
        'imp-cliente-abogado' => 'imprimir.time.imp-cliente-abogado',
        'imp-abogado-cliente' => 'imprimir.time.imp-abogado-cliente',
        'imp-liquidacion'     => 'imprimir.time.imp-liquidacion',
        'exp-liquidacion'     => 'imprimir.time.exp-liquidacion',
        'exp-analisis'        => 'imprimir.time.exp-analisis',
    ];

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

    public function analisis(Request $request, \App\Services\TimeAnalisisService $service)
    {
        $this->validate($request, [
            'desde' => 'required|date_format:d/m/Y',
            'hasta' => 'required|date_format:d/m/Y',
        ]);
        return $this->sendResponse($service->calcular($request), trans('api.success'));
    }

    public function gestiones(Request $request)
    {
        \App\Services\TimePermisos::exigir($request->user(), \App\Services\TimePermisos::ANALISIS);
        return $this->sendResponse(
            \App\Models\TimeGestion::orderBy('nombre')->get(['id', 'nombre']),
            trans('api.success')
        );
    }

    /**
     * Las impresiones web no tienen sesion: se emite una URL firmada (5 min) que
     * identifica a quien la pide. Los permisos se validan aca y de nuevo al abrirla.
     */
    public function urlFirmada(Request $request)
    {
        $this->validate($request, ['tipo' => 'required|in:' . implode(',', array_keys(self::TIPOS_FIRMADOS))]);

        $tipo = $request->get('tipo');
        if (in_array($tipo, ['imp-liquidacion', 'exp-liquidacion'], true)) {
            TimePermisos::exigir($request->user(), TimePermisos::LIQUIDACION);
        } elseif ($tipo === 'exp-analisis') {
            TimePermisos::exigir($request->user(), TimePermisos::ANALISIS);
        } else {
            TimeHoraRepository::usuarioForzado($request); // informes viejos: por time_level
        }

        $params = $request->except(['tipo', 'uid', 'signature', 'expires']);
        $params['uid'] = $request->user()->id;

        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            self::TIPOS_FIRMADOS[$request->get('tipo')], now()->addMinutes(5), $params, false
        );

        return $this->sendResponse(['url' => $url], trans('api.success'));
    }

    public function liquidacion(Request $request, \App\Services\LiquidacionService $service)
    {
        TimePermisos::exigir($request->user(), TimePermisos::LIQUIDACION);
        $data = $service->calcular(
            $request->get('desde'),
            $request->get('hasta'),
            $request->get('cliente_id', null)
        );
        return $this->sendResponse($data, trans('api.success'));
    }
}
