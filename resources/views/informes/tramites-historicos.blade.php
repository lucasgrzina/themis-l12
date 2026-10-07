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
            <th>Nro. Expediente</th>
        @endif
        <td>Cliente</td>
        <td>Fecha</td>
        <td>Empresa Ref.</td>
        <td>Abogados Resp.</td>
        <td>Tipo Trámite</td>
        <td>Fecha Archivo</td>
        <td>Usuario Archivo</td>
    </tr>
    @foreach($data as $row)
        <tr>
            @if ($request['area_id'] == 1)
                <td>{{ $row->expediente }}</td>
            @endif

            <td>{{ $row->nombre_cliente }}</td>
            <td>{{ $row->tramite_f_inicio === null ? '' : format_date($row->tramite_f_inicio) }}</td>
            <td>{{ $row->empresas_referencia }}</td>
            <td>{{ $row->responsables }}</td>
            <td>{{ $row->tipo_tramite }}</td>
            <td>{{ $row->fecha_archivo === null ? '' : format_date($row->fecha_archivo,'d/m/Y H:i','Y-m-d H:i:s') }}</td>
            <td>{{ $row->usuario_archivo }}</td>
        </tr>
    @endforeach
</table>
<table>
    <tr><td>Cantidad de registros:</td><td>{{ count($data) }}</td></tr>
</table>