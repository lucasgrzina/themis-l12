   <table>
        <?php $idant = 0;?>
        @foreach($data as $tramite)
            @if($tramite->cliente_id != $idant)
                <tr><td colspan="7"></td></tr>
                <tr>
                  <th style="background-color: #ccc;">Cliente</th>
                  <th style="background-color: #ccc;">ID Requerimiento</th>
                  <th style="background-color: #ccc;">ID Tramite</th>
                  <th style="background-color: #ccc;">Expediente</th>
                  <th style="background-color: #ccc;">Estado</th>
                  <th style="background-color: #ccc;">Fecha Inicio</th>
                  <th style="background-color: #ccc;">Archivado</th>
                  <th style="background-color: #dddddd;">VINCULAR</th>
                </tr>

                <?php $idant = $tramite->cliente_id;?>
            @endif
            <tr>
              <td>{{ $tramite->cliente->nombre_completo }}</td>
              <td>{{ $tramite->requerimiento_id }}</td>
              <td>{{ $tramite->id }}</td>
              <td>{{ $tramite->expediente }}</td>
              <td>{{ $tramite->estado->nombre }}</td>
              <td>{{ $tramite->fecha_inicio }}</td>
              <td>{{ $tramite->archivar == 1 ? 'SI' : 'NO' }}</td>
              <td></td>
            </tr>
        @endforeach
    </table>
    