<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Agregados de horas del modulo Time para el informe "Analisis de horas".
 *
 * Permiso: time-analisis-de-horas:R (ver todo el estudio). Sin el permiso: 403.
 * Las horas en estado 'corriendo' (cronometro activo) nunca suman.
 */
class TimeAnalisisService
{
    private const TOP_MATRIZ = 10;

    public function calcular(Request $request)
    {
        TimePermisos::exigir($request->user(), TimePermisos::ANALISIS);
        $forzado = null;
        [$desde, $hasta, $filtros] = $this->parametros($request, $forzado);

        $actual = $this->base($desde, $hasta, $filtros);

        // Periodo anterior de igual duracion, inmediatamente previo.
        $dias = $desde->diffInDays($hasta) + 1;
        $prevHasta = $desde->copy()->subDay();
        $prevDesde = $prevHasta->copy()->subDays($dias - 1);
        $minutosPrev = (int) $this->base($prevDesde, $prevHasta, $filtros)->sum('h.minutos');

        $tot = (clone $actual)->selectRaw(
            'COALESCE(SUM(h.minutos),0) as minutos, COUNT(*) as movimientos,
             COUNT(DISTINCT h.user_id) as abogados, COUNT(DISTINCT h.cliente_id) as clientes,
             COUNT(DISTINCT h.fecha) as dias_con_carga,
             COALESCE(SUM(CASE WHEN h.facturar = 1 THEN h.minutos ELSE 0 END),0) as facturables'
        )->first();

        $minutos = (int) $tot->minutos;

        $porAbogado = (clone $actual)
            ->join('users as u', 'u.id', '=', 'h.user_id')
            ->selectRaw('h.user_id as id, u.name as nombre, SUM(h.minutos) as minutos, COUNT(*) as movimientos')
            ->groupBy('h.user_id', 'u.name')->orderByDesc('minutos')->get();

        $porCliente = (clone $actual)
            ->join('clientes as c', 'c.id', '=', 'h.cliente_id')
            ->selectRaw('h.cliente_id as id, c.nombre_completo as nombre, SUM(h.minutos) as minutos, COUNT(*) as movimientos')
            ->groupBy('h.cliente_id', 'c.nombre_completo')->orderByDesc('minutos')->get();

        $porGestion = (clone $actual)
            ->leftJoin('time_gestiones as g', 'g.id', '=', 'h.gestion_id')
            ->selectRaw("h.gestion_id as id, COALESCE(g.nombre,'Sin gestión') as nombre, SUM(h.minutos) as minutos, COUNT(*) as movimientos")
            ->groupBy('h.gestion_id', 'g.nombre')->orderByDesc('minutos')->get();

        $porDia = (clone $actual)
            ->selectRaw('h.fecha as fecha, SUM(h.minutos) as minutos')
            ->groupBy('h.fecha')->orderBy('h.fecha')->get();

        $detalle = (clone $actual)
            ->join('users as u', 'u.id', '=', 'h.user_id')
            ->join('clientes as c', 'c.id', '=', 'h.cliente_id')
            ->selectRaw('h.user_id, u.name as abogado, h.cliente_id, c.nombre_completo as cliente,
                         SUM(h.minutos) as minutos, COUNT(*) as movimientos')
            ->groupBy('h.user_id', 'u.name', 'h.cliente_id', 'c.nombre_completo')
            ->orderByDesc('minutos')->get();

        // Desglose por referencia solo cuando se acota a 1 abogado y 1 cliente (nivel 3).
        $porReferencia = [];
        if (count($filtros['user_ids']) === 1 && count($filtros['cliente_ids']) === 1) {
            $porReferencia = (clone $actual)
                ->selectRaw("COALESCE(NULLIF(h.referencia,''),'Sin referencia') as nombre, SUM(h.minutos) as minutos, COUNT(*) as movimientos")
                ->groupBy('nombre')->orderByDesc('minutos')->get();
        }

        return [
            'periodo' => [
                'desde' => $desde->toDateString(), 'hasta' => $hasta->toDateString(), 'dias' => $dias,
                'desde_prev' => $prevDesde->toDateString(), 'hasta_prev' => $prevHasta->toDateString(),
            ],
            'alcance_propio' => (bool) $forzado,
            'totales' => [
                'minutos'        => $minutos,
                'minutos_prev'   => $minutosPrev,
                'movimientos'    => (int) $tot->movimientos,
                'abogados'       => (int) $tot->abogados,
                'clientes'       => (int) $tot->clientes,
                'facturables'    => (int) $tot->facturables,
                'no_facturables' => $minutos - (int) $tot->facturables,
                'promedio_dia'   => $tot->dias_con_carga ? round($minutos / $tot->dias_con_carga) : 0,
            ],
            'por_abogado'    => $this->normaliza($porAbogado),
            'por_cliente'    => $this->normaliza($porCliente),
            'por_gestion'    => $this->normaliza($porGestion),
            'por_dia'        => $this->normaliza($porDia),
            'por_referencia' => $this->normaliza($porReferencia),
            'detalle'        => $this->normaliza($detalle),
            'matriz'         => $this->matriz($detalle, $porAbogado, $porCliente),
        ];
    }


    /** Filtros comunes a la pantalla y al Excel . */
    private function parametros(Request $request, $forzado)
    {
        $desde = Carbon::createFromFormat('d/m/Y', $request->get('desde'))->startOfDay();
        $hasta = Carbon::createFromFormat('d/m/Y', $request->get('hasta'))->startOfDay();

        $filtros = [
            'user_ids'    => $forzado ? [$forzado] : $this->ids($request->get('user_ids')),
            'cliente_ids' => $this->ids($request->get('cliente_ids')),
            'gestion_ids' => $this->ids($request->get('gestion_ids')),
            'facturar'    => $request->get('facturar'),
            'estado'      => $request->get('estado'),
            'categoria'   => $request->get('categoria_abogado'),
            'referencia'  => trim((string) $request->get('referencia', '')),
        ];
        return [$desde, $hasta, $filtros];
    }

    /** Una fila por hora cargada, con los mismos filtros y permisos que la pantalla. */
    public function listado(Request $request)
    {
        TimePermisos::exigir($request->user(), TimePermisos::ANALISIS);
        $forzado = null;
        [$desde, $hasta, $filtros] = $this->parametros($request, $forzado);

        $rows = $this->base($desde, $hasta, $filtros)
            ->join('users as u', 'u.id', '=', 'h.user_id')
            ->join('clientes as c', 'c.id', '=', 'h.cliente_id')
            ->leftJoin('time_gestiones as g', 'g.id', '=', 'h.gestion_id')
            ->orderBy('h.fecha')->orderBy('u.name')->orderBy('c.nombre_completo')->orderBy('h.id')
            ->get(['h.fecha', 'u.name as abogado', 'c.nombre_completo as cliente', 'g.nombre as gestion',
                   'h.referencia', 'h.descripcion', 'h.minutos', 'h.facturar', 'h.estado']);

        return ['desde' => $desde, 'hasta' => $hasta, 'rows' => $rows];
    }

    private function base(Carbon $desde, Carbon $hasta, array $f)
    {
        $q = DB::table('time_horas as h')
            ->whereNull('h.deleted_at')
            ->where('h.estado', '!=', 'corriendo')
            ->where('h.fecha', '>=', $desde->toDateString())
            ->where('h.fecha', '<=', $hasta->toDateString());

        if ($f['user_ids'])    $q->whereIn('h.user_id', $f['user_ids']);
        if ($f['cliente_ids']) $q->whereIn('h.cliente_id', $f['cliente_ids']);
        if ($f['gestion_ids']) $q->whereIn('h.gestion_id', $f['gestion_ids']);
        if ($f['facturar'] !== null && $f['facturar'] !== '') {
            $q->where('h.facturar', (int) $f['facturar']);
        }
        if (in_array($f['estado'], ['pendiente', 'vinculada'], true)) {
            $q->where('h.estado', $f['estado']);
        }
        if ($f['categoria']) {
            $q->whereIn('h.user_id', function ($s) use ($f) {
                $s->select('id')->from('users')->where('categoria_abogado', $f['categoria']);
            });
        }
        if ($f['referencia'] !== '') {
            $q->where('h.referencia', 'like', '%' . $f['referencia'] . '%');
        }
        return $q;
    }

    /** Top N abogados x top N clientes; el resto se agrupa en "Otros". */
    private function matriz($detalle, $porAbogado, $porCliente)
    {
        $topU = $porAbogado->take(self::TOP_MATRIZ)->pluck('nombre', 'id');
        $topC = $porCliente->take(self::TOP_MATRIZ)->pluck('nombre', 'id');

        $celdas = [];
        foreach ($detalle as $d) {
            $u = $topU->has($d->user_id) ? $d->user_id : 0;
            $c = $topC->has($d->cliente_id) ? $d->cliente_id : 0;
            $k = $u . '|' . $c;
            $celdas[$k] = ($celdas[$k] ?? 0) + (int) $d->minutos;
        }

        $abogados = $topU->values()->all();
        $clientes = $topC->values()->all();
        if ($porAbogado->count() > self::TOP_MATRIZ) $abogados[] = 'Otros';
        if ($porCliente->count() > self::TOP_MATRIZ) $clientes[] = 'Otros';

        $uIds = array_merge($topU->keys()->all(), $porAbogado->count() > self::TOP_MATRIZ ? [0] : []);
        $cIds = array_merge($topC->keys()->all(), $porCliente->count() > self::TOP_MATRIZ ? [0] : []);

        $valores = [];
        foreach ($uIds as $u) {
            $fila = [];
            foreach ($cIds as $c) {
                $fila[] = $celdas[$u . '|' . $c] ?? 0;
            }
            $valores[] = $fila;
        }

        return ['abogados' => $abogados, 'clientes' => $clientes, 'valores' => $valores];
    }

    private function normaliza($rows)
    {
        return collect($rows)->map(function ($r) {
            $r = (array) $r;
            if (isset($r['minutos']))     $r['minutos'] = (int) $r['minutos'];
            if (isset($r['movimientos'])) $r['movimientos'] = (int) $r['movimientos'];
            return $r;
        })->values()->all();
    }

    private function ids($v)
    {
        if ($v === null || $v === '') return [];
        return array_values(array_filter(array_map('intval', (array) $v)));
    }
}
