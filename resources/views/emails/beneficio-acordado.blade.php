@component('mail::message')
Estimado/a Sr./a. <strong>{{ $tramite->cliente->nombre_completo }}</strong>

En esta oportunidad nos ponemos en contacto con Ud. a fin de comunicarle que el día {{ $tramite->fecha_beneficio }} ha sido otorgado su beneficio de {{ $tramite->requerimiento->tipoTramite->nombre }} bajo el número {{ $tramite->expediente }}.

Dentro de los próximos 60 días estará percibiendo el primer pago de su haber jubilatorio, momento en que estaremos tomando contacto con UD. a fin de precisarle lugar y fecha de pago, como así también los importes a cobrar.

Quedando a su disposición por cualquier consulta, saludamos a Ud. atte.

Campos Valeiras Abogados.
@endcomponent
