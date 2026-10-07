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
        <td>Expediente</td>
        <th><strong>Cliente</strong></th>
        <th>Fecha</th>
        <td>Juzgado</td>
        @if($request['area_id'] == 1)
            <th>Conciliación</th>
        @endif
    </tr>
    @foreach($data as $row)
        <tr>
            <td>{{ $row->nro_expediente }}</td>
            <td>{{ ($row->tramite && $row->tramite->cliente ? $row->tramite->cliente->nombre_completo : '--') }}</td>
            <td>{{ $row->fecha }}</td>
            <td>{{ $row->juzgado['nombre'] }}</td>
            <td>
                @if($request['area_id'] == 1)
                    @if ($row->juicio_conciliado)
                        <strong>Nro: </strong>{{ $row->nro_conciliacion }}<br>
                        <strong>Fecha: </strong>{{ $row->fecha_conciliacion }}<br>                        
                    @else
                        NO
                    @endif
                @endif
            </td>
        </tr>
    @endforeach
</table>
<table>
    <tr><td>Cantidad de registros:</td><td>{{ count($data) }}</td></tr>
</table>