<?php
$host = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
?>
<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="author" content="Identidad Digital">
        <meta name="description" content="{{ config('app.name') }}">
        <meta name="version" content="{{ env('ASSETS_VERSION') }}">
        <base href="/">

        <title>{{ config('app.name') }}</title>
        
        @if($host !== 'themis.dev')
            <link href="{{ asset('/css/vendors.css') }}?version={{ env('ASSETS_VERSION') }}" rel="stylesheet">
        @else
            <link href="{{ elixir('/css/vendors.css') }}?version={{ env('ASSETS_VERSION') }}" rel="stylesheet">
        @endif
        <style type="text/css">
            .main-header {
                background: #003366;
            }
            .main-sidebar {
                z-index: 1031;
            }
            @if(app()->environment('local'))
            /* desplaza la app (incluidos header/sidebar fixed) para que la barra no los tape */
            #app { transform: translateY(16px); }
            @keyframes ambiente-prueba-parpadeo {
                0%, 49%   { background: #e00000bf; color: #ffffff85; }
                50%, 100% { background: #ffffff85;    color: #e00000bf; }
            }
            .ambiente-prueba {
                position: fixed;
                left: 0;
                right: 0;
                top: 0;
                height: 16px;
                line-height: 16px;
                background: #e00000bf;
                color: #fff;
                font-size: 9px;
                font-weight: bold;
                font-style: italic;
                text-align: center;
                text-transform: uppercase;
                z-index: 99999;
                pointer-events: none;
                animation: ambiente-prueba-parpadeo 1s steps(1, end) infinite;
            }
            @endif
        </style>
        <script>
            window.Laravel = {!! json_encode([
                'csrfToken' => csrf_token(),
                'siteName'  => config('app.name'),
                'apiDomain' => config('app.url').'/api',
                'webDomain' => str_replace('127.0.0.1:8000','themis.local',route('imprimir.index'))
            ]) !!}
        </script>
    </head>

    <body class="skin-blue-light">
        <div class="cargando-themis">
            <p class="row" style="font-size: 13px;font-weight: bold;padding-top: 100px;">
                <div class="col-xs-12 text-center">
                    <img src="{{ config('app.url') }}/img/logo1.jpg"><br>    
                </div>
                <span class="col-xs-12 text-center">Cargando Temis. Espero unos minutos por favor.</span>
                
            </p>
        </div>
        <div id="app"></div>
        @if(app()->environment('local'))
            <div class="ambiente-prueba">AMBIENTE DE PRUEBA</div>
        @endif
        @if($host !== 'themis.local')
            <script src="{{ asset('/js/app.js') }}?version={{ env('ASSETS_VERSION') }}"></script>
        @else
            <script src="{{ elixir('/js/app.js') }}?version={{ env('ASSETS_VERSION') }}"></script>
        @endif
    </body>
</html>
