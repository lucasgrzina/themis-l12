<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\TimeClienteParametro;
use App\Models\TimeHora;
use App\Models\TimeParticipacion;
use App\Models\TimeParticipacionExterna;
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

    /**
     * Participaciones externas vigentes para el cliente/periodo.
     * Default (periodo null) + override del mes; el override pisa al default
     * para la misma clave (nombre + base). Ordenadas por base y orden.
     *
     * @return \Illuminate\Support\Collection<TimeParticipacionExterna>
     */
    private function participacionesExternas($cliente_id, $periodoMes)
    {
        $parts = TimeParticipacionExterna::where('cliente_id', $cliente_id)
            ->where(function ($q) use ($periodoMes) {
                $q->whereNull('periodo')->orWhere('periodo', $periodoMes);
            })
            ->orderBy('base')->orderBy('orden')->orderBy('nombre')->get();

        $porClave = [];
        foreach ($parts as $p) {
            $clave = mb_strtolower(trim($p->nombre)) . '|' . $p->base;
            // override (periodo seteado) pisa al default (periodo null)
            if (!isset($porClave[$clave]) || $p->periodo !== null) {
                $porClave[$clave] = $p;
            }
        }

        return collect(array_values($porClave))->sortBy([
            ['base', 'asc'],
            ['orden', 'asc'],
            ['nombre', 'asc'],
        ])->values();
    }

    /**
     * Tarifa aplicable a una categoria de abogado (fallback a la base).
     * Devuelve ['clave' => categoria en minusculas o '' si es la base, 'categoria' => ..., 'valor' => float].
     */
    private function tarifaPara($categoria, $tarifas)
    {
        if ($categoria) {
            foreach ($tarifas as $t) {
                if ($t->categoria !== null && mb_strtolower(trim($t->categoria)) === mb_strtolower(trim($categoria))) {
                    return ['clave' => mb_strtolower(trim($t->categoria)), 'categoria' => $t->categoria, 'valor' => (float) $t->valor_hora];
                }
            }
        }
        foreach ($tarifas as $t) {
            if ($t->categoria === null) {
                return ['clave' => '', 'categoria' => null, 'valor' => (float) $t->valor_hora];
            }
        }
        return ['clave' => '', 'categoria' => null, 'valor' => 0.0];
    }

    /** Parametros del cliente (hs director, % director, hs piso) vigentes al periodo. */
    private function parametrosCliente($cliente_id, $desde)
    {
        $p = TimeClienteParametro::where('cliente_id', $cliente_id)
            ->where(function ($q) use ($desde) {
                $q->whereNull('vigencia_desde')->orWhere('vigencia_desde', '<=', $desde);
            })
            ->orderByDesc('vigencia_desde')->orderByDesc('id')->first();

        return [
            'horas_director' => $p ? (float) $p->horas_director : 0.0,
            'pct_director' => $p ? (float) $p->pct_director : 0.0,
            'horas_piso' => $p ? (float) $p->horas_piso : 0.0,
        ];
    }

    /** Una tarifa vigente por categoria (la mas reciente aplicable), la base primero. */
    private function cuadroTarifas($tarifas, $tienePiso)
    {
        $out = [];
        $vistas = [];
        foreach ($tarifas as $t) {
            $clave = $t->categoria === null ? '' : mb_strtolower(trim($t->categoria));
            if (isset($vistas[$clave])) {
                continue; // ya tomamos la mas reciente
            }
            $vistas[$clave] = true;
            $out[] = [
                'clave' => $clave,
                'categoria' => $t->categoria,
                'etiqueta' => $clave === ''
                    ? ($tienePiso ? 'HORA ABONO' : 'HORA')
                    : 'HORA ' . mb_strtoupper(trim($t->categoria)),
                'valor' => (float) $t->valor_hora,
            ];
        }
        usort($out, fn ($a, $b) => ($a['clave'] === '' ? 0 : 1) <=> ($b['clave'] === '' ? 0 : 1));
        return $out;
    }

    private function calcularHoras($cliente, $desde, $hasta, $tc, $ret)
    {
        // Solo tarifas vigentes al período (vigencia_desde <= desde, o sin vigencia).
        // Ordenadas desc para que tarifaPara() tome la más reciente aplicable.
        $tarifas = TimeTarifa::where('cliente_id', $cliente->id)
            ->where(function ($q) use ($desde) {
                $q->whereNull('vigencia_desde')->orWhere('vigencia_desde', '<=', $desde);
            })
            ->orderByDesc('vigencia_desde')->get();

        $param = $this->parametrosCliente($cliente->id, $desde);
        $minPiso = (int) round($param['horas_piso'] * 60);
        $minDirector = (int) round($param['horas_director'] * 60);
        $tienePiso = $minPiso > 0;

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

        $minPorUser = [];
        foreach ($horasPorUser as $h) {
            $minPorUser[$h->user_id] = (int) $h->min;
        }
        // Participantes sin horas en el periodo: igual se listan (en 0) para poder editarlas en el XLSX
        foreach ($pctPorUser as $uid => $pct) {
            if ($pct > 0 && !isset($minPorUser[$uid])) {
                $minPorUser[$uid] = 0;
            }
        }

        $usuarios = \App\User::whereIn('id', array_keys($minPorUser))
            ->get(['id', 'name', 'categoria_abogado'])->keyBy('id');

        // El Director ya no se carga como usuario: sus horas salen de los parametros del cliente.
        // Se ignora un eventual usuario legado con categoria 'Director' para no duplicarlo.
        foreach (array_keys($minPorUser) as $uid) {
            $cat = isset($usuarios[$uid]) ? $usuarios[$uid]->categoria_abogado : null;
            if ($cat !== null && mb_strtolower(trim($cat)) === 'director') {
                unset($minPorUser[$uid]);
            }
        }

        $factorNeto = 1 - ($ret['imp_cto_dto'] + $ret['sircreb'] + $ret['ret_gcias']);
        $tarifaBase = $this->tarifaPara(null, $tarifas);

        $abogados = [];
        $totalParticipaciones = 0.0;
        $minReales = 0;

        foreach ($minPorUser as $uid => $min) {
            $categoria = isset($usuarios[$uid]) ? $usuarios[$uid]->categoria_abogado : null;
            $tf = $this->tarifaPara($categoria, $tarifas);
            $horas = $min / 60.0;
            $brutoUsd = $horas * $tf['valor'];
            $baseParticipacion = $horas * ($tf['valor'] * $factorNeto) * $tc; // en $
            $pct = isset($pctPorUser[$uid]) ? $pctPorUser[$uid] : 0.0;
            $participacion = $baseParticipacion * $pct;

            $minReales += $min;
            $totalParticipaciones += $participacion;

            $abogados[] = [
                'user_id' => $uid,
                'abogado' => isset($usuarios[$uid]) ? $usuarios[$uid]->name : '',
                'categoria' => $categoria,
                'tarifa_clave' => $tf['clave'],
                'minutos' => (int) $min,
                'horas' => round($horas, 4),
                'valor_hora' => round($tf['valor'], 2),
                'bruto_usd' => round($brutoUsd, 2),
                'base_participacion' => round($baseParticipacion, 2),
                'pct' => $pct,
                'participacion' => round($participacion, 2),
                'es_director' => false,
            ];
        }

        usort($abogados, fn ($a, $b) => strcmp($a['abogado'], $b['abogado']));

        // Director (parametros del cliente): se valua a la tarifa base
        if ($minDirector > 0 || $param['pct_director'] > 0) {
            $horas = $minDirector / 60.0;
            $baseParticipacion = $horas * $tarifaBase['valor'] * $factorNeto * $tc;
            $participacion = $baseParticipacion * $param['pct_director'];
            $totalParticipaciones += $participacion;
            $abogados[] = [
                'user_id' => null,
                'abogado' => 'Director',
                'categoria' => 'Director',
                'tarifa_clave' => '',
                'minutos' => $minDirector,
                'horas' => round($horas, 4),
                'valor_hora' => round($tarifaBase['valor'], 2),
                'bruto_usd' => round($horas * $tarifaBase['valor'], 2),
                'base_participacion' => round($baseParticipacion, 2),
                'pct' => $param['pct_director'],
                'participacion' => round($participacion, 2),
                'es_director' => true,
            ];
        }

        $cuadro = $this->cuadroTarifas($tarifas, $tienePiso);
        $lineas = $this->lineasFacturables($abogados, $cuadro, $tarifaBase, $minPiso, $minDirector, $minReales);

        $totalUsd = 0.0;
        foreach ($lineas as $l) {
            $totalUsd += $l['minutos'] / 60.0 * $l['valor_hora'];
        }

        $totalArs = $totalUsd * $tc;
        $impCto = $totalArs * $ret['imp_cto_dto'];
        $sircreb = $totalArs * $ret['sircreb'];
        $retGcias = $totalArs * $ret['ret_gcias'];
        $netoCva = $totalArs - $impCto - $sircreb - $retGcias - $totalParticipaciones;

        return [
            'cliente_id' => $cliente->id,
            'cliente' => $cliente->nombre_completo,
            'modelo' => 'horas',
            'tarifas' => $cuadro,
            'lineas' => $lineas,
            'horas_piso' => $param['horas_piso'],
            'minutos_piso' => $minPiso,
            'minutos_reales' => $minReales,
            'minutos_excedente' => $tienePiso ? max(0, $minReales - $minPiso) : 0,
            'horas_director' => $param['horas_director'],
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

    /**
     * Lineas facturables (concepto, valor hora, minutos).
     *
     * Sin piso: las horas de cada abogado se agrupan por su tarifa (base o categoria).
     * Con piso: se factura SIEMPRE el piso a tarifa base y el excedente (horas reales
     * - piso) se asigna primero a las horas de los abogados con tarifa propia mayor a la
     * base (hasta lo que trabajaron, de mayor a menor valor); el resto va a tarifa base.
     * Las horas de director se facturan aparte, a tarifa base.
     */
    private function lineasFacturables($abogados, $cuadro, $tarifaBase, $minPiso, $minDirector, $minReales)
    {
        $etiquetaCat = fn ($t) => mb_strtoupper(trim($t['categoria']));

        $director = [];
        if ($minDirector > 0) {
            $director[] = [
                'tipo' => 'director', 'etiqueta' => 'HORAS DIRECTOR', 'tarifa_clave' => '',
                'valor_hora' => $tarifaBase['valor'], 'minutos' => $minDirector,
            ];
        }

        if ($minPiso > 0) {
            $lineas = [[
                'tipo' => 'piso', 'etiqueta' => 'HORAS ABONO', 'tarifa_clave' => '',
                'valor_hora' => $tarifaBase['valor'], 'minutos' => $minPiso,
            ]];
            $lineas = array_merge($lineas, $director);

            $restante = max(0, $minReales - $minPiso);

            // Tarifas propias mayores a la base, de mayor a menor
            $premium = array_values(array_filter($cuadro, fn ($t) => $t['clave'] !== '' && $t['valor'] > $tarifaBase['valor']));
            usort($premium, fn ($a, $b) => $b['valor'] <=> $a['valor']);

            foreach ($premium as $t) {
                $trabajados = 0;
                foreach ($abogados as $a) {
                    if (!$a['es_director'] && $a['tarifa_clave'] === $t['clave']) {
                        $trabajados += $a['minutos'];
                    }
                }
                $cubre = min($restante, $trabajados);
                $restante -= $cubre;
                $lineas[] = [
                    'tipo' => 'excedente', 'etiqueta' => 'HORAS EXCEDENTES ' . $etiquetaCat($t),
                    'tarifa_clave' => $t['clave'], 'valor_hora' => $t['valor'], 'minutos' => $cubre,
                ];
            }

            $lineas[] = [
                'tipo' => 'excedente', 'etiqueta' => 'HORAS EXCEDENTES', 'tarifa_clave' => '',
                'valor_hora' => $tarifaBase['valor'], 'minutos' => $restante,
            ];
            return $lineas;
        }

        // Sin piso: horas agrupadas por tarifa. La base siempre figura (aunque sea en 0).
        $porClave = ['' => 0];
        foreach ($abogados as $a) {
            if ($a['es_director']) {
                continue;
            }
            $porClave[$a['tarifa_clave']] = (isset($porClave[$a['tarifa_clave']]) ? $porClave[$a['tarifa_clave']] : 0) + $a['minutos'];
        }
        $horasLineas = [];
        foreach ($cuadro as $t) {
            if (array_key_exists($t['clave'], $porClave)) {
                $horasLineas[] = [
                    'tipo' => 'horas',
                    'etiqueta' => $t['clave'] === '' ? 'HORAS' : 'HORAS ' . $etiquetaCat($t),
                    'tarifa_clave' => $t['clave'], 'valor_hora' => $t['valor'], 'minutos' => $porClave[$t['clave']],
                ];
            }
        }
        if (!$horasLineas) {
            $horasLineas[] = [
                'tipo' => 'horas', 'etiqueta' => 'HORAS', 'tarifa_clave' => '',
                'valor_hora' => $tarifaBase['valor'], 'minutos' => $porClave[''],
            ];
        }
        return array_merge($horasLineas, $director);
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

        // Participaciones a colaboradores externos (no-usuarios).
        // base='subtotal'      -> % sobre el subtotal sin IVA, se descuenta ANTES del subtotal neto.
        // base='subtotal_neto' -> % sobre el subtotal neto, se descuenta DESPUES.
        $extParts = $this->participacionesExternas($cliente->id, $periodoMes);

        $partSubtotal = [];
        $partSubtotalNeto = [];
        $totalExtSubtotal = 0.0;
        foreach ($extParts as $p) {
            if ($p->base === 'subtotal_neto') {
                $partSubtotalNeto[] = $p;
                continue;
            }
            $monto = $subtotal * (float) $p->porcentaje;
            $totalExtSubtotal += $monto;
            $partSubtotal[] = [
                'nombre' => $p->nombre,
                'porcentaje' => (float) $p->porcentaje,
                'base' => 'subtotal',
                'monto' => round($monto, 2),
            ];
        }

        $subtotalNeto = $conIva - $impCto - $sircreb - $ivaRet - $retGcias - $totalExtSubtotal;

        $partNetoItems = [];
        $totalExtNeto = 0.0;
        foreach ($partSubtotalNeto as $p) {
            $monto = $subtotalNeto * (float) $p->porcentaje;
            $totalExtNeto += $monto;
            $partNetoItems[] = [
                'nombre' => $p->nombre,
                'porcentaje' => (float) $p->porcentaje,
                'base' => 'subtotal_neto',
                'monto' => round($monto, 2),
            ];
        }

        $netoCva = $subtotalNeto - $totalExtNeto;

        $participacionesExternas = array_merge($partSubtotal, $partNetoItems);

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
            'participaciones_externas' => $participacionesExternas,
            'participaciones_externas_subtotal' => round($totalExtSubtotal, 2),
            'participaciones_externas_neto' => round($totalExtNeto, 2),
            'participaciones_externas_pendiente' => $extParts->isEmpty(),
            'subtotal_neto' => round($subtotalNeto, 2),
            'neto_cva' => round($netoCva, 2),
        ];
    }
}
