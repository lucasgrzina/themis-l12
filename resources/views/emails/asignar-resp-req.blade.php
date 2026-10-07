@component('mail::message')
Fuiste asigndo/a a un nuevo requerimiento:

<strong>Nro: </strong>{{ $req->id }}<br>
<strong>Cliente: </strong>{{ $req->cliente->nombre_completo }}<br>
<strong>Tipo de trámite: </strong>{{ $req->tipoTramite->nombre }}<br>
<strong>Estado: </strong>{{ $req->estado->nombre }}<br>

@endcomponent
