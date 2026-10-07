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
        <th>Cliente</th>
        <th>Responsables</th>

        @if($request['area_id'] == 1)
            <th>Nro. Expediente</th>
            <th>Rep.Origen</th>
            <th>Clave Seg. Social</th>
        @endif

        @if ($request['area_id'] == 2 || $request['area_id'] == 3 || $request['area_id'] == 4)
            <th>Autos</th>
            <th>Parte</th>
        @endif

        <th>Tipo Trámite</th>
        <th>Fecha Inicio</th>
        <th>Fecha Beneficio</th>
        <th>Documento</th>
        <th>Estado Trámite</th>

    </tr>
    @foreach($data as $row)
        <tr>
            <td>{{ $row->nombre_cliente }}</td>
            <td>{{ $row->responsables }}</td>
            
            @if ($request['area_id'] == 1)
                <td>{{ $row->expediente }}</td>
                <td>{{ $row->rep_origen }}</td>
                <td>{{ $row->clave_seguridad_social }}</td>
            @endif

            @if ($request['area_id'] == 2 || $request['area_id'] == 3 || $request['area_id'] == 4)
                <td>{{ $row->autos }}</td>
                <td>{{ $row->parte }}</td>
            @endif

            <td>{{ $row->tipo_tramite }}</td>
            <td>{{ $row->tramite_f_inicio === null ? '' : format_date($row->tramite_f_inicio) }}</td>
            <td>{{ $row->fecha_beneficio === null ? '' : format_date($row->fecha_beneficio) }}</td>
            <td>{{ $row->documento }}</td>
            <td>{{ $row->estado_tramite }}</td>
        </tr>
    @endforeach
</table>
<table>
    <tr><td>Cantidad de registros:</td><td>{{ count($data) }}</td></tr>
</table>