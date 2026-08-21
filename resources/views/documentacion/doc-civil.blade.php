@extends('documentacion.template')
@section('content')
<p style="text-align: right;">Buenos Aires, ${hoy}.-</p>
<strong>${cliente}</strong>
<br><br>
<p style="text-align: center;">Ref.: "${titulo}"</p>
De nuestra consideración:
<br><br>
Conforme la situación planteada, cumplimos en emitir el presente, a saber:
<br><br>
<strong style="text-decoration: underline">I.- DOCUMENTACIÓN NECESARIA:</strong>
<br><br>
Será necesaria la siguiente documentación en original, la que puede ser facilitada por ustedes o bien obtenida mediante gestoría con cargo adicional:
<br><br>
${doc_tt}
<br><br>
${doc_tc}
<br><br>
Quedamos a vuestra disposición para todo lo referido al presente.
@endsection