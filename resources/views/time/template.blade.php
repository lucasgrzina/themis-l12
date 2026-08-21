<!DOCTYPE html>
<html style="background:#fff!important;">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
    <style>
      @page { margin-top: 100px; margin-bottom: 100px; }
      header { position: fixed; top: -80px; left: 0px; right: 0px; height: 30px; }
      footer { position: fixed; bottom: -90px; left: 0px; right: 0px; height: 30px; text-align: center;}
    </style>    
  </head>
  <body style="background:#fff!important;">
  <header>
      <img src="{{ asset('img/logo-doc.jpg') }}" style="width:200px;" />
      <span style="width:100%;background: #00529e;height:4px;display: block;"></span>
      <span style="width:100%;background: #bcbec0;height:4px;display: block;"></span>
  </header>
  
    <div class="content-doc">
      @yield('content')
    </div>
    
    <footer><strong>Av. Córdoba 1255 Piso 4 Of. A (C1055AAC) CABA | 4815-2410 / 4815-9453</strong></footer>    
  </body>
</html>