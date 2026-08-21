@component('mail::message')
Hola {{ $destinatario->name }}, {{ $aviso->user->name }} te ha asignado un nuevo aviso en el cliente {{ $aviso->cliente->nombre_completo }}.

<strong>Motivo: </strong>{{ $aviso->motivo }}<br>

@endcomponent