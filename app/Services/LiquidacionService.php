<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\TimeHora;
use App\Models\TimeParticipacion;
use App\Models\TimePresupuesto;
use App\Models\TimeRetencion;
use App\Models\TimeTarifa;
use App\Models\TimeTipoCambio;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Motor de calculo del informe de liquidacion de honorarios del Time.
 *
 * Replica la planilla mensual "Cliente/Abogado": toma las horas cargadas en el
 * Time y aplica valor hora (por categoria de abogado), tipo de cambio,
 * retenciones y participaciones, obteniendo el NETO CVA.
 *
 * Soporta dos modelos de cliente: 'horas' y 'presupuesto'.
 */
class LiquidacionService
{
    /**
     * Calcula la liquidacion para un periodo y, opcionalmente, un cliente.
     *
     * @return array {desde, hasta, tc, retenciones, clientes:[...]}
     */
    public function calcular($desde, $hasta, $cliente_id = null)
    {
        $desde = Carbon::createFromFormat('d/m/Y', $desde)->format('Y-m-d');
        $hasta = Carbon::createFromFormat('d/m/Y', $hasta)->format('Y-m-d');

        $tc = $this->tipoCambio($desde);
        $ret = $this->retenciones($desde);

        // Clientes a liquidar: los que tienen horas en el periodo + los de presupuesto con monto en el periodo
        $clientesIds = $this->clientesConActividad($desde, $hasta, $cliente_id);

        $clientes = [];
        foreach ($clientesIds as $cid) {
            $cliente = Cliente::find($cid);
            if (!$cliente) {
                continue;
            }
            if ($cliente->modelo_facturacion === 'presupuesto') {
                $clientes[] = $this->calcularPresupuesto($cliente, $desde, $ret);
            } else {
                $clientes[] = $this->calcularHoras($cliente, $desde, $hasta, $tc, $ret);
            }
        }

        return [
            'desde' => $desde,
            'hasta' => $hasta,
            'tc' => $tc,
            'retenciones' => $ret,
            'clientes' => array_values(array_filter($clientes)),
        ];
    }

    private function clientesConActividad($desde, $hasta, $cliente_id)
    {
        if ($cliente_id) {
            return [(int) $cliente_id];
        }
        $conHoras = TimeHora::whereBetween('fecha', [$desde, $hasta])
            ->distinct()->pluck('cliente_id')->toArray();
        $periodoMes = Carbon::parse($desde)->startOfMonth()->format('Y-m-d');
        $conPresup = TimePresupuesto::where('periodo', $periodoMes)
            ->distinct()->pluck('cliente_id')->toArray();
        return array_values(array_unique(array_merge($conHoras, $conPresup)));
    }

    /** Tipo de cambio del mes del periodo (o el mas reciente anterior). */
    private function tipoCambio($desde)
    {
        $mes = Carbon::parse($desde)->startOfMonth()->format('Y-m-d');
        $tc = TimeTipoCambio::where('periodo', $mes)->orderByDesc('id')->first();
        if (!$tc) {
            $tc = TimeTipoCambio::where('periodo', '<=', $desde)->orderByDesc('periodo')->first();
        }
        return $tc ? (float) $tc->valor : 0.0;
    }

    /** Tasas de retencion vigentes al periodo, por codigo. */
    private function retenciones($desde)
    {
        $codigos = ['imp_cto_dto', 'sircreb', 'ret_gcias', 'iva'];
        $out = [];
        foreach ($codigos as $cod) {
            $r = TimeRetencion::where('codigo', $cod)
                ->where(function ($q) use ($desde) {
                    $q->whereNull('vigencia_desde')->orWhere('vigencia_desde', '<=', $desde);
                })
                ->orderByDesc('vigencia_desde')->first();
            $out[$cod] = $r ? (float) $r->tasa : 0.0;
        }
        return $out;
    }

    /** Valor hora de un abogado para un cliente segun su categoria (fallback a base). */
    private function valorHora($cliente_id, $categoria, $tarifas)
    {
        if ($categoria) {
            foreach ($tarifas as $t) {
                if ($t->categoria !== null && mb_strtolower(trim($t->categoria)) === mb_strtolower(trim($categoria))) {
                    return (float) $t->valor_hora;
                }
            }
        }
        foreach ($tarifas as $t) {
            if ($t->categoria === null) {
                return (float) $t->valor_hora;
            }
        }
        return 0.0;
    }

    private function calcularHoras($cliente, $desde, $hasta, $tc, $ret)
    {
        // Solo tarifas vigentes al período (vigencia_desde <= desde, o sin vigencia).
        // Ordenadas desc para que valorHora() tome la más reciente aplicable.
        $tarifas = TimeTarifa::where('cliente_id', $cliente->id)
            ->where(function ($q) use ($desde) {
                $q->whereNull('vigencia_desde')->orWhere('vigencia_desde', '<=', $desde);
            })
            ->orderByDesc('vigencia_desde')->get();

        // Participaciones: default (periodo null) + override del mes
        $periodoMes = Carbon::parse($desde)->startOfMonth()->format('Y-m-d');
        $parts = TimeParticipacion::where('cliente_id', $cliente->id)
            ->where(function ($q) use ($periodoMes) {
                $q->whereNull('periodo')->orWhere('periodo', $periodoMes);
            })->get();
        $pctPorUser = [];
        foreach ($parts as $p) {
            $uid = $p->user_id;
            // override (periodo seteado) pisa al default (periodo null)
            if (!isset($pctPorUser[$uid]) || $p->periodo !== null) {
                $pctPorUser[$uid] = (float) $p->porcentaje;
            }
        }

        $horasPorUser = TimeHora::select('user_id', DB::raw('SUM(minutos) as min'))
            ->where('cliente_id', $cliente->id)
            ->whereBetween('fecha', [$desde, $hasta])
            ->groupBy('user_id')->get();

        $factorNeto = 1 - ($ret['imp_cto_dto'] + $ret['sircreb'] + $ret['ret_gcias']);

        $abogados = [];
        $totalUsd = 0.0;
        $totalParticipaciones = 0.0;

        $userIds = $horasPorUser->pluck('user_id')->all();
        $nombres = \App\User::whereIn('id', $userIds)->pluck('name', 'id');
        $categorias = \App\User::whereIn('id', $userIds)->pluck('categoria_abogado', 'id');

        foreach ($horasPorUser as $h) {
            $horas = $h->min / 60.0;
            $categoria = $categorias[$h->user_id] ?? null;
            $valorHora = $this->valorHora($cliente->id, $categoria, $tarifas);
            $brutoUsd = $horas * $valorHora;
            $netoValor = $valorHora * $factorNeto;
            $baseParticipacion = $horas * $netoValor * $tc; // en $
            $pct = $pctPorUser[$h->user_id] ?? 0.0;
            $participacion = $baseParticipacion * $pct;

            $totalUsd += $brutoUsd;
            $totalParticipaciones += $participacion;

            $abogados[] = [
                'user_id' => $h->user_id,
                'abogado' => $nombres[$h->user_id] ?? '',
                'categoria' => $categoria,
                'minutos' => (int) $h->min,
                'horas' => round($horas, 4),
                'valor_hora' => round($valorHora, 2),
                'bruto_usd' => round($brutoUsd, 2),
                'base_participacion' => round($baseParticipacion, 2),
                'pct' => $pct,
                'participacion' => round($participacion, 2),
            ];
        }

        usort($abogados, fn ($a, $b) => strcmp($a['abogado'], $b['abogado']));

        $totalArs = $totalUsd * $tc;
        $impCto = $totalArs * $ret['imp_cto_dto'];
        $sircreb = $totalArs * $ret['sircreb'];
        $retGcias = $totalArs * $ret['ret_gcias'];
        $netoCva = $totalArs - $impCto - $sircreb - $retGcias - $totalParticipaciones;

        return [
            'cliente_id' => $cliente->id,
            'cliente' => $cliente->nombre_completo,
            'modelo' => 'horas',
            'abogados' => $abogados,
            'total_usd' => round($totalUsd, 2),
            'total_ars' => round($totalArs, 2),
            'imp_cto_dto' => round($impCto, 2),
            'sircreb' => round($sircreb, 2),
            'ret_gcias' => round($retGcias, 2),
            'total_participaciones' => round($totalParticipaciones, 2),
            'neto_cva' => round($netoCva, 2),
        ];
    }

    private function calcularPresupuesto($cliente, $desde, $ret)
    {
        $periodoMes = Carbon::parse($desde)->startOfMonth()->format('Y-m-d');
        $presupuestos = TimePresupuesto::where('cliente_id', $cliente->id)
            ->where('periodo', $periodoMes)->get();

        if ($presupuestos->isEmpty()) {
            return null;
        }

        $items = [];
        $subtotal = 0.0;
        $conIva = 0.0;
        $ivaRet = 0.0;
        foreach ($presupuestos as $p) {
            $st = (float) $p->subtotal;
            $ci = $p->aplica_iva ? $st * (1 + $ret['iva']) : $st;
            $subtotal += $st;
            $conIva += $ci;
            $ivaRet += $p->aplica_iva ? $st * $ret['iva'] : 0.0;
            $items[] = [
                'descripcion' => $p->descripcion,
                'subtotal' => round($st, 2),
                'aplica_iva' => (bool) $p->aplica_iva,
                'con_iva' => round($ci, 2),
            ];
        }

        $impCto = $conIva * $ret['imp_cto_dto'];
        $sircreb = $conIva * $ret['sircreb'];
        $retGcias = $subtotal * $ret['ret_gcias'];
        // Participaciones a colaboradores externos: PENDIENTE (no-usuarios)
        $participacionesExternas = 0.0;
        $subtotalNeto = $conIva - $impCto - $sircreb - $ivaRet - $retGcias - $participacionesExternas;
        $netoCva = $subtotalNeto;

        return [
            'cliente_id' => $cliente->id,
            'cliente' => $cliente->nombre_completo,
            'modelo' => 'presupuesto',
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'con_iva' => round($conIva, 2),
            'imp_cto_dto' => round($impCto, 2),
            'sircreb' => round($sircreb, 2),
            'iva_ret' => round($ivaRet, 2),
            'ret_gcias' => round($retGcias, 2),
            'participaciones_externas_pendiente' => true,
            'subtotal_neto' => round($subtotalNeto, 2),
            'neto_cva' => round($netoCva, 2),
        ];
    }
}
