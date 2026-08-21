<table>
    @foreach($filters as $k => $v)
            <tr>
                <td>{{ $k }}</td>
                <td>{{ $v }}</td>
            </tr>
    @endforeach
</table>

<table>
    @foreach($data as $row)
    <tbody>
        <tr>
          <th style="width:30%;background-color: #f5f5f5;">Cliente</th>
          <th style="width:20%;background-color: #f5f5f5;">CUIT/L</th>
          <th style="width:25%;background-color: #f5f5f5;">Exp. Ppal.</th>
          <th style="width:25%;background-color: #f5f5f5;">Beneficio</th>
        </tr>
        <tr >
          <td style="background-color: #ffffff;"><span style="text-transform:uppercase;">{{ $row->cliente->nombre_completo }}</span></td>
          <td style="background-color: #ffffff;">{{$row->cliente->cuit}}</td>
          <td style="background-color: #ffffff;">{{$row->tramite_ant['expediente']}}</td>
          <td style="background-color: #ffffff;">{{$row->nro_beneficio}}</td>
        </tr>
        <tr>
            <th colspan="2" style="text-align:center;background-color: #f5f5f5;">Fecha vuelta Anses</th>
            <th colspan="2" style="text-align:center;background-color: #f5f5f5;">Fecha Vto. (120 días)</th>
        </tr>
        <tr>
            <td colspan="2" style="text-align:center;background-color: #ffffff;">{{ $row->fecha_remision }}</td>
            <td colspan="2" style="text-align:center;background-color: #ffffff;">{{ $row->fecha_remision_vto }}</td>
        </tr>
        @if ($row->deceased)
        <tr>
            <th colspan="2" style="text-align:center;background-color: #f5f5f5;">Nombre Conyuge</th>
            <th colspan="2" style="text-align:center;background-color: #f5f5f5;">Documento Conyuge</th>
        </tr>
        <tr>
            <td colspan="2" style="text-align:center;background-color: #ffffff;">{{ $row->cliente->nombre_conyuge ? $row->cliente->nombre_conyuge . ' ' . $row->cliente->apellido_conyuge : '--'}}</td>
            <td colspan="2" style="text-align:center;background-color: #ffffff;">{{ $row->cliente->nro_doc_conyuge ? $row->cliente->nro_doc_conyuge : '--' }}</td>
        </tr>
        @endif        
        <tr>
            <th style="text-align:right;background-color: #f5f5f5;">Fecha remisión</th>
            <th style="text-align:center;background-color: #f5f5f5;">Estado</th>
            <th colspan="2" style="text-align:left;background-color: #f5f5f5;">Observación</th>
        </tr>
        @if(isset($row->ultimoEstadioAnses))
            <tr >
                <td style="text-align:right;background-color: #ffffff;">{{ $row->ultimoEstadioAnses->fecha_remision }}</td>
                <td style="text-align:center;background-color: #ffffff;">{{ $row->ultimoEstadioAnses->estado->nombre }}</td>
                <td colspan="2" style="text-align:left;background-color: #ffffff;">{{ $row->ultimoEstadioAnses->observations }}</td>
            </tr>
        @else
            @foreach($row->anses as $subrow)
            <tr >
                <td style="text-align:right;background-color: #ffffff;">{{ $subrow->fecha_remision }}</td>
                <td style="text-align:center;background-color: #ffffff;">{{ $subrow->estado->nombre }}</td>
                <td colspan="2" style="text-align:left;background-color: #ffffff;">{{ $subrow->observations }}</td>
            </tr>
            @endforeach

        @endif 
        <tr><td colspan="4" style="text-align:right;"></td></tr>
        <tr><td colspan="4" style="text-align:right;"></td></tr>
    </tbody>
    @endforeach
</table>
<table>
    <tr><td>Cantidad de registros:</td><td>{{ count($data) }}</td></tr>
</table>