@extends('documentacion.template')
@section('content')
<p style="text-align: right;">Buenos Aires, ${hoy}.-</p>
<strong>${cliente}</strong><br><br>

Por medio de la presente le solicitamos la documentación detallada, a fin de iniciar su trámite.
<br><br>
<span style="width:100%;background: #000000;height:2px;display: block;"></span>
<br><br>
<strong style="font-size: 17px;">Documentación Personal</strong>
<br><br>
${doc_tt}
<br>
${doc_tc}
@endsection