<table>
    @foreach($filters as $k => $v)
            <tr>
                <td>{{ $k }}</td>
                <td>{{ $v }}</td>
            </tr>
    @endforeach
</table>

<table>
    <tbody>
        <tr>
          <th>Estado Req.</th>
          <th>Tipo Tramite</th>
          @if($request['area_id'] == 1)
          <th>Expediente</th>
          @endif
          @if($request['area_id'] == 5)
          <th>Nro. Trámite</th>
          @endif
          <th>Fecha Inicio</th>

          @if($request['area_id'] == 1)
          <th>Rep. Origen</th>
          @endif

          @if($request['area_id'] == 2 || $request['area_id'] == 3 || $request['area_id'] == 4)
              <th>Autos</th>
              <th>Parte</th>
          @endif

          <th>Est. Tramite</th>

          @if($request['area_id'] == 1)
              <th>Nro. Benef.</th>
              <th>Fecha Benef.</th>
          @endif

          @if($request['area_id'] == 2)
              <th>Nro. Seclo</th>
              <th>Fecha Seclo</th>
          @endif

          @if($request['area_id'] == 3 || $request['area_id'] == 4)
              <th>Mediación</th>
              <th>Fecha Mediación</th>
          @endif
        </tr>
    </tbody>
    <?php $count = 0; ?>
    @foreach($data['data'] as $row)

    <tbody>

        @if(is_array($row) && $row['tipo'] === 'CABECERA')

            <tr>
              <td colspan="8" style="background-color: #cac8f3;">
                <span style="text-transform:uppercase;font-weight:bold;">({{ $row['id_cliente'] }}) {{ $row['nombre_cliente'] }}</span>
                <span style="margin-left:10px;">Fecha Alta: {{ $row['fecha_alta'] }}</span>
              </td>
            </tr>
        @else
            <?php $count++; ?>
            <tr>
              <td> {{ $row->estado_requerimiento }} </td>
              <td> {{ $row->tipo_tramite }} </td>
              @if($request['area_id'] == 1)
              <td> {{ $row->expediente }} </td>
              @endif
              @if($request['area_id'] == 5)
              <td> {{ $row->expediente }} </td>
              @endif
              <td> {{ $row->tramite_f_inicio}} </td>
              @if($request['area_id'] == 1)
              <td> {{ $row->rep_origen }} </td>
              @endif
              @if($request['area_id'] == 2 || $request['area_id'] == 3 || $request['area_id'] == 4)
                  <td>{{ $row->autos }}</td>
                  <td>{{ $row->parte }}</td>
              @endif       
              <td> {{ $row->estado_tramite }} </td>
              <td> {{ $row->nro_beneficio }} </td>
              <td> {{ $row->fecha_beneficio }} </td>
            </tr>
        @endif
    </tbody>
    @endforeach
</table>
<table>
    <tr><td>Cantidad de registros:</td><td>{{ $count }}</td></tr>
</table>