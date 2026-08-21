<table>
    @foreach($filters as $k => $v)
    <tr>
        <td>{{ $k }}</td>
        <td>{{ $v }}</td>
    </tr>
    @endforeach
</table>

<table>
    <tr>
        @if($request['area_id'] == 1)
            <td>Cliente - CUIT/Beneficio</td>
            <td>Rep.Origen</td>
            <td>Fecha Beneficio</td>
        @endif

        @if($request['area_id'] == 2)
            <td>Cliente - CUIT/Seclo</td>
            <td>Autos / Parte</td>
            <td>Fecha</td>
        @endif

        @if($request['area_id'] == 3 || $request['area_id'] == 4)
            <td>Cliente - CUIT/Mediación</td>
            <td>Autos / Parte</td>
            <td>Fecha</td>
        @endif

        <td>Empresa</td>
        <td>Colega</td>
        <td>Responsables</td>
        <td>Fecha Cobro</td>
        <td>Detalle</td>

    </tr>
    @foreach($data as $row)
        <tr>
            <td>
                {{ $row->nombre_cliente }}{{ ($row->categoria == 'E' ? ' (EMPRESA)' : '') }}<br>
                {{ ($row->cuit ? $row->cuit : 'S/C') }}<br>
                {{ $row->nro_beneficio }}
            </td>            

            @if ($request['area_id'] == 1) 
                <td>{{ $row->rep_origen }}</td>
                <td>{{ $row->fecha_beneficio }}</td>
            @endif
            @if ($request['area_id'] == 2 || $request['area_id'] == 3 || $request['area_id'] == 4)
                <td>{{ $row->autos }} / {{ $row->parte }}</td>
                <td>{{ $row->fecha_beneficio }}</td>";
            @endif
            <td>{{ $row->empresa }}</td>
            <td>{{ $row->colega }}</td>
            <td>{{ $row->responsables }}</td>
            <td>{{ $row->fecha_cobro }}</td>
            <td>
                <?php $detalle = json_decode($row->detalle,true); ?>

                @if ($request['area_id'] == 1)
                    <strong>Haber mensual:</strong><span> $ {{ number_format($detalle['haber_mensual'],2,',','.') }}</span><br>
                    <strong>Retroactivo:</strong><span> $ {{ number_format($detalle['retroactivo'],2,',','.') }}</span><br>
                    <strong>Mes alta:</strong><span>{{ $detalle['mes_alta'] }}</span><br>
                    <strong>Agente Pagador:</strong><span>{{ $detalle['agente_pagador'] }}</span>
                @elseif ($request['area_id'] == 2)
                    <strong>Cuota acordada:</strong><br><span> $ {{ number_format($detalle['cuota_acordada'],2,',','.') }}</span><br>
                    <strong>Honorarios:</strong><br><span> $ {{ number_format($detalle['honorarios'],2,',','.') }}</span><br>
                @else
                    <strong>Cuota acordada:</strong><br><span> $ {{ number_format($detalle['cuota_acordada'],2,',','.') }}</span><br>
                    <strong>Retroactivo:</strong><br><span> $ {{ number_format($detalle['retroactivo'],2,',','.') }}</span><br>
                    <strong>Honorarios:</strong><br><span> $ {{ number_format($detalle['honorarios'],2,',','.') }}</span><br>
                @endif
            </td>
        </tr>
    @endforeach
</table>
<table>
    <tr><td>Cantidad de registros:</td><td>{{ count($data) }}</td></tr>
</table>