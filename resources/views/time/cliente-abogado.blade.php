@extends('time.template')
@section('content')
<style>
.page-break {
    page-break-after: always;
}
.table th{
	border-top: 1px solid #000000;
	border-bottom: 1px solid #000000;
	padding-bottom: 12px;
	padding-top: 12px;
}
.table td, .table th{
	font-size: 11px;
}
.table .pie td{
	padding-top: 12px;
}
.table .items td{
	padding-top: 10px;
	padding-bottom: 10px;
}
</style>
<table style="width: 100%" cellspacing="0">
    <tbody>
        <tr>
          <th style="text-align:right;">Desde: </th>
          <td style="text-align:left;">{{ ($filtros['desde']) }}</td>
          <th style="text-align:right;">Hasta: </th>
          <td style="text-align:left;">{{ ($filtros['hasta']) }}</td>          
        </tr>
    </tbody>
</table>
<br>
<table style="width: 100%" cellspacing="0">
    <tbody>
        <tr>
        	<td colspan="4" style="text-align:center;background:#ddd;">
        		<h3 style="text-transform:uppercase;font-weight:bold;font-size: 14px;">Horas del Estudio CVA Cliente: {{ $filtros['cliente'] }}</h3>
        	</td>
        </tr>
    </tbody>
</table>
@foreach($data as $i => $row)
	@if (isset($row['tipo']) && $row['tipo'] === 'CABECERA')
		@if ($i > 0)
			  	</tbody>
			</table>
		@endif
		<h2 style="text-transform:uppercase;font-weight:bold;font-size: 14px;">Referencia: {{ $row['referencia'] }}</h2>
		<table class="table" style="width: 100%" cellspacing="0">
		    <tbody>
	            <tr>
	              <th>Fecha</th>
	              <th>Abogado</th>
	              <th>Gestión</th>
	              <th>Descripción</th>
	              <th class="text-center" style="width: 50px;text-align:center;">Horas</th>
	              <th class="text-center" style="width: 40px;text-align:center;">Factura</th>
	            </tr>    		
	@endif
	@if (isset($row['tipo']) && $row['tipo'] === 'PIE')
        <tr class="tr-pie">
          <td colspan="4" style="text-align:right;">
          	<span style="text-transform:uppercase;font-weight:bold;">Total:</span>
          </td>
          <td style="background:#eee;border-color: #eee;text-align:center;">
          	<span style="text-transform:uppercase;font-weight:bold;">{{ minutosToHoras($row['minutos']) }}</span>
          </td>
          <td class="text-center" style="">&nbsp;</td>
        </tr>	
    @endif
    @if (isset($row['tipo']) && $row['tipo'] === 'TOTAL')
    	</tbody>
	</table>
	<br><br><br>
	<table class="table" cellspacing="0" style="width: 100%;border:1px solid;">
		<tbody>
        <tr class="tr-total">
          <td style="text-align:right;background:#eee;border-color: #eee;">
          	<span style="text-transform:uppercase;font-weight:bold;">Total:</span>
          </td>
          <td class="text-center" style="background:#ddd;border-color: #ddd;text-align:center;width: 100px;">
          	<span style="text-transform:uppercase;font-weight:bold;">{{ minutosToHoras($row['minutos']) }}</span>
          </td>
          <td class="text-center" style="background:#eee;border-color: #eee;text-align:center;width: 150px;">{{ $row['movimientos'] }} movimiento(s)</td>
        </tr>	
        </tbody>
	</table>
    @endif
    @if (isset($row['id']))
	    <tr class="tr-items">
	      <td> {{ format_date($row['fecha']) }} </td>
	      <td> {{ $row['usuario'] ? $row['usuario']['name'] : '' }} </td>
	      <td> {{ $row['gestion'] ? $row['gestion']['nombre'] : '' }} </td>
	      <td> {{ $row['descripcion'] }} </td>
	          <td class="text-center" style="background:#eee;border-color: #eee;text-align:center;"> {{ minutosToHoras($row['minutos']) }} </td>
	          <td style="text-align:center;">
	          	{{ $row['facturar'] ? 'Si' : 'No' }} 
	          </td>
	    </tr> 
    @endif   

@endforeach
@if ($i > 0)
	  	</tbody>
	</table>
@endif
@endsection