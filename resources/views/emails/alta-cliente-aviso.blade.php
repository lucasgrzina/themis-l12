@component('mail::message')
Hola {{ $destinatario->name }}, {{ $creador->name }} dio de alta un nuevo cliente y te asignó un aviso.

<strong>Cliente: </strong>{{ $cliente->nombre_completo }}@if($cliente->cuit) ({{ $cliente->cuit }})@endif<br>
<strong>Motivo del aviso: </strong>{{ $aviso->motivo }}<br>
@if($observacion)
<strong>Observación: </strong>{{ $observacion }}<br>
@endif
@if(count($documentos) > 0)
<strong>Documentos adjuntos: </strong>{{ count($documentos) }} (también disponibles en la solapa Docs del cliente)<br>
@endif

@endcomponent
