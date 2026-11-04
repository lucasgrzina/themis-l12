<?php
if (!function_exists('fmt')) { function fmt($n) { return number_format((float)$n, 2, ',', '.'); } }
if (!function_exists('ffecha')) { function ffecha($d) { return $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : ''; } }
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
    h2 { font-size: 15px; margin: 0 0 2px 0; }
    .periodo { font-size: 11px; color: #555; margin-bottom: 10px; }
    .cliente { page-break-inside: avoid; margin-bottom: 22px; }
    .cliente-h { background: #2c3e50; color: #fff; padding: 6px 10px; font-size: 13px; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; }
    .det th { background: #ecf0f1; border: 1px solid #bdc3c7; padding: 4px 6px; text-align: left; }
    .det td { border: 1px solid #ddd; padding: 4px 6px; }
    .num { text-align: right; }
    .tot { width: 55%; margin-top: 8px; float: right; }
    .tot td { padding: 3px 8px; }
    .tot .lbl { text-align: right; color: #555; }
    .tot .val { text-align: right; border-bottom: 1px solid #eee; }
    .neto td { font-weight: bold; font-size: 13px; background: #eafaf1; border-top: 2px solid #27ae60; }
    .clear { clear: both; }
    .nota { color: #c0392b; font-size: 10px; margin-top: 4px; }
    .muted { color: #888; }
</style>
</head>
<body>
    <h2>Liquidación de honorarios — Cliente / Abogado</h2>
    <div class="periodo">Período: {{ ffecha($data['desde']) }} al {{ ffecha($data['hasta']) }} &nbsp;·&nbsp; TC MEP: {{ fmt($data['tc']) }}</div>

    @forelse($data['clientes'] as $c)
    <div class="cliente">
        <div class="cliente-h">{{ $c['cliente'] }} <span style="font-weight:normal;font-size:11px;">({{ $c['modelo'] == 'presupuesto' ? 'Presupuesto' : 'Por horas' }})</span></div>

        @if($c['modelo'] == 'horas')
        <table class="det">
            <thead>
                <tr>
                    <th>Abogado</th><th>Categoría</th><th class="num">Horas</th>
                    <th class="num">Valor hora</th><th class="num">Bruto U$S</th>
                    <th class="num">Part. %</th><th class="num">Participación $</th>
                </tr>
            </thead>
            <tbody>
                @foreach($c['abogados'] as $a)
                <tr>
                    <td>{{ $a['abogado'] }}</td>
                    <td>{{ $a['categoria'] ?: '—' }}</td>
                    <td class="num">{{ fmt($a['horas']) }}</td>
                    <td class="num">{{ fmt($a['valor_hora']) }}</td>
                    <td class="num">{{ fmt($a['bruto_usd']) }}</td>
                    <td class="num">{{ fmt($a['pct'] * 100) }}%</td>
                    <td class="num">{{ fmt($a['participacion']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <table class="tot">
            <tr><td class="lbl">Total U$S</td><td class="val">{{ fmt($c['total_usd']) }}</td></tr>
            <tr><td class="lbl">Total $ (x TC)</td><td class="val">{{ fmt($c['total_ars']) }}</td></tr>
            <tr><td class="lbl">Imp. al cto y dto</td><td class="val">-{{ fmt($c['imp_cto_dto']) }}</td></tr>
            <tr><td class="lbl">SIRCREB</td><td class="val">-{{ fmt($c['sircreb']) }}</td></tr>
            <tr><td class="lbl">Ret. Ganancias</td><td class="val">-{{ fmt($c['ret_gcias']) }}</td></tr>
            <tr><td class="lbl">Participaciones</td><td class="val">-{{ fmt($c['total_participaciones']) }}</td></tr>
            <tr class="neto"><td class="lbl">NETO CVA</td><td class="val">{{ fmt($c['neto_cva']) }}</td></tr>
        </table>
        <div class="clear"></div>

        @else
        <table class="det">
            <thead>
                <tr><th>Descripción</th><th class="num">Subtotal $</th><th>IVA</th><th class="num">Con IVA $</th></tr>
            </thead>
            <tbody>
                @foreach($c['items'] as $it)
                <tr>
                    <td>{{ $it['descripcion'] ?: '—' }}</td>
                    <td class="num">{{ fmt($it['subtotal']) }}</td>
                    <td>{{ $it['aplica_iva'] ? 'Sí' : 'No' }}</td>
                    <td class="num">{{ fmt($it['con_iva']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <table class="tot">
            <tr><td class="lbl">Subtotal $</td><td class="val">{{ fmt($c['subtotal']) }}</td></tr>
            <tr><td class="lbl">Con IVA</td><td class="val">{{ fmt($c['con_iva']) }}</td></tr>
            <tr><td class="lbl">Imp. al cto y dto</td><td class="val">-{{ fmt($c['imp_cto_dto']) }}</td></tr>
            <tr><td class="lbl">SIRCREB</td><td class="val">-{{ fmt($c['sircreb']) }}</td></tr>
            <tr><td class="lbl">IVA</td><td class="val">-{{ fmt($c['iva_ret']) }}</td></tr>
            <tr><td class="lbl">Ret. Ganancias</td><td class="val">-{{ fmt($c['ret_gcias']) }}</td></tr>
            <tr class="neto"><td class="lbl">NETO CVA</td><td class="val">{{ fmt($c['neto_cva']) }}</td></tr>
        </table>
        <div class="clear"></div>
        @if(!empty($c['participaciones_externas_pendiente']))
        <div class="nota">* No incluye participaciones de colaboradores externos (pendiente de definir).</div>
        @endif
        @endif
    </div>
    @empty
        <p class="muted">No hay datos para el período seleccionado.</p>
    @endforelse
</body>
</html>
