<?php
if (!function_exists('xf')) { function xf($n) { return round((float)$n, 2); } }
if (!function_exists('xfecha')) { function xfecha($d) { return $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : ''; } }
?>
<table>
    <thead>
        <tr><th colspan="7">Liquidación de honorarios — Cliente / Abogado</th></tr>
        <tr>
            <th>Período</th>
            <th>{{ xfecha($data['desde']) }} al {{ xfecha($data['hasta']) }}</th>
            <th>TC MEP</th>
            <th>{{ xf($data['tc']) }}</th>
            <th></th><th></th><th></th>
        </tr>
        <tr><th></th></tr>
    </thead>
    <tbody>
        @foreach($data['clientes'] as $c)
            <tr><td colspan="7"><strong>{{ $c['cliente'] }} ({{ $c['modelo'] == 'presupuesto' ? 'Presupuesto' : 'Por horas' }})</strong></td></tr>
            @if($c['modelo'] == 'horas')
                <tr>
                    <td><strong>Abogado</strong></td><td><strong>Categoría</strong></td>
                    <td><strong>Horas</strong></td><td><strong>Valor hora</strong></td>
                    <td><strong>Bruto U$S</strong></td><td><strong>Part. %</strong></td>
                    <td><strong>Participación $</strong></td>
                </tr>
                @foreach($c['abogados'] as $a)
                <tr>
                    <td>{{ $a['abogado'] }}</td>
                    <td>{{ $a['categoria'] ?: '' }}</td>
                    <td>{{ xf($a['horas']) }}</td>
                    <td>{{ xf($a['valor_hora']) }}</td>
                    <td>{{ xf($a['bruto_usd']) }}</td>
                    <td>{{ xf($a['pct'] * 100) }}</td>
                    <td>{{ xf($a['participacion']) }}</td>
                </tr>
                @endforeach
                <tr><td colspan="6">Total U$S</td><td>{{ xf($c['total_usd']) }}</td></tr>
                <tr><td colspan="6">Total $ (x TC)</td><td>{{ xf($c['total_ars']) }}</td></tr>
                <tr><td colspan="6">Imp. al cto y dto</td><td>{{ xf(-$c['imp_cto_dto']) }}</td></tr>
                <tr><td colspan="6">SIRCREB</td><td>{{ xf(-$c['sircreb']) }}</td></tr>
                <tr><td colspan="6">Ret. Ganancias</td><td>{{ xf(-$c['ret_gcias']) }}</td></tr>
                <tr><td colspan="6">Participaciones</td><td>{{ xf(-$c['total_participaciones']) }}</td></tr>
                <tr><td colspan="6"><strong>NETO CVA</strong></td><td><strong>{{ xf($c['neto_cva']) }}</strong></td></tr>
            @else
                <tr>
                    <td><strong>Descripción</strong></td><td><strong>Subtotal $</strong></td>
                    <td><strong>IVA</strong></td><td><strong>Con IVA $</strong></td>
                    <td></td><td></td><td></td>
                </tr>
                @foreach($c['items'] as $it)
                <tr>
                    <td>{{ $it['descripcion'] ?: '' }}</td>
                    <td>{{ xf($it['subtotal']) }}</td>
                    <td>{{ $it['aplica_iva'] ? 'Sí' : 'No' }}</td>
                    <td>{{ xf($it['con_iva']) }}</td>
                    <td></td><td></td><td></td>
                </tr>
                @endforeach
                <tr><td colspan="6">Subtotal $</td><td>{{ xf($c['subtotal']) }}</td></tr>
                <tr><td colspan="6">Con IVA</td><td>{{ xf($c['con_iva']) }}</td></tr>
                <tr><td colspan="6">Imp. al cto y dto</td><td>{{ xf(-$c['imp_cto_dto']) }}</td></tr>
                <tr><td colspan="6">SIRCREB</td><td>{{ xf(-$c['sircreb']) }}</td></tr>
                <tr><td colspan="6">IVA</td><td>{{ xf(-$c['iva_ret']) }}</td></tr>
                <tr><td colspan="6">Ret. Ganancias</td><td>{{ xf(-$c['ret_gcias']) }}</td></tr>
                <tr><td colspan="6"><strong>NETO CVA</strong></td><td><strong>{{ xf($c['neto_cva']) }}</strong></td></tr>
            @endif
            <tr><td></td></tr>
        @endforeach
    </tbody>
</table>
