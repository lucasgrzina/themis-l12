@component('mail::message')
Estimado/a Sr./a. <strong>{{ $tramite->cliente->nombre_completo }}</strong>

En esta oportunidad nos ponemos en contacto con Ud. a fin de comunicarle que el día {{ $tramite->fecha_inicio }} hemos iniciado el trámite de {{ $tramite->requerimiento->tipoTramite->nombre }} bajo el número {{ $tramite->expediente }}.

Quedando a su disposición por cualquier consulta, saludamos a Ud. atte.

Campos Valeiras Abogados.

@endcomponent
