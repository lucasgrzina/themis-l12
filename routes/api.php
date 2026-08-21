<?php
Route::post('upload/docs', 'UploadsApiController@docs');

Route::post('authenticate', 'AuthenticateController@authenticate');
Route::post('forgot-password', 'ForgotPasswordAPIController@forgot');

Route::get('general-data','GeneralDataAPIController@index');
Route::post('general-datas',function() {
    return response()->json([]);
});


Route::group(['middleware' => ['jwt.auth']], function()
{
    Route::group(['prefix' => 'combos'],function() {
        Route::get('usuarios','CombosAPIController@usuarios');
        Route::get('roles','CombosAPIController@roles');
        Route::get('acciones','CombosAPIController@acciones');
        Route::get('cond-iva','CombosAPIController@condIva');
        Route::get('areas','CombosAPIController@areas');
        Route::get('doc-requerida','CombosAPIController@docRequerida');
        Route::get('paises','CombosAPIController@paises');
        Route::get('provincias','CombosAPIController@provincias');
        Route::get('tipo-doc','CombosAPIController@tipoDoc');
        Route::get('colegas','CombosAPIController@colegas');
        Route::get('tipo-tramites','CombosAPIController@tipoTramites');
        Route::get('responsables/{areaId}','CombosAPIController@responsables');
        Route::get('doc-requerida-tt/{ttId}','CombosAPIController@docRequeridaTT');
        Route::get('doc-requerida-ta/{ttId}','CombosAPIController@docRequeridaTA');
        Route::get('am-cliente','CombosAPIController@amCliente');
        Route::get('solapa-requerimientos/{areaId}','CombosAPIController@solapaRequerimientos');
        Route::get('solapa-tramites/{areaId}','CombosAPIController@solapaTramites');
        Route::get('solapa-avisos','CombosAPIController@solapaAvisos');
        Route::get('exp-judicial/{areaId}','CombosAPIController@expJudicial');
        Route::group(['prefix' => 'informes'],function() {
            Route::get('ucadep/{areaId}','CombosAPIController@infUcadep');
            Route::get('beneficios/{areaId}','CombosAPIController@infBeneficios');
            Route::get('tramites/{areaId}','CombosAPIController@infBeneficios');
            Route::get('requerimientos-empresas/{areaId}','CombosAPIController@infReqEmpresas');
        });
        Route::group(['prefix' => 'time'],function() {
            Route::get('abogados','CombosAPIController@timeAbogados');
            Route::get('clientes/{con_horas?}','CombosAPIController@timeClientes');
            Route::get('am-horas','CombosAPIController@timeAMHoras');
            Route::get('referencias/{cliente_id}','CombosAPIController@timeReferencias');
        });        
        //Route::get('solapa-time-horas','CombosAPIController@solapaTimeHoras');
    });

    Route::get('avisos-clientes/vencimientos', 'AvisoClienteAPIController@vencimientos');
    Route::get('avisos-clientes/pendientes/cant', 'AvisoClienteAPIController@cantPendientes');
    Route::get('avisos-clientes/pendientes', 'AvisoClienteAPIController@pendientes');
    Route::put('avisos-clientes/descartar/{id}', 'AvisoClienteAPIController@descartar');
    Route::put('avisos-clientes/posponer/{id}', 'AvisoClienteAPIController@posponer');
    //Route::resource('avisos-clientes', 'AvisoClienteAPIController');

    Route::get('dashboard', 'DashboardAPIController@index');
    Route::get('user', 'UserController@user');

    Route::post('user/password/update', 'UserController@updatePassword');

    Route::delete('areas/eliminar-seleccion', 'AreaAPIController@removeSelected');
    Route::resource('areas', 'AreaAPIController');
    
    /*Route::delete('clientes/requerimientos/{id}', 'ClienteAPIController@destroyRequerimientos');
    Route::put('clientes/requerimientos/{id}', 'ClienteAPIController@updateRequerimientos');
    Route::post('clientes/requerimientos', 'ClienteAPIController@storeRequerimientos');*/

    /*Route::delete('clientes/observaciones/{id}', 'ClienteAPIController@destroyObservaciones');
    Route::put('clientes/observaciones/{id}', 'ClienteAPIController@updateObservaciones');
    Route::post('clientes/observaciones', 'ClienteAPIController@storeObservaciones');*/
    /*Route::delete('clientes/documentos/{id}', 'ClienteAPIController@destroyDocumentos');
    Route::put('clientes/documentos/{id}', 'ClienteAPIController@updateDocumentos');
    Route::post('clientes/documentos', 'ClienteAPIController@storeDocumentos');*/
    Route::delete('clientes/{id}/eliminar-seleccion', 'ClienteAPIController@removeSelected');

    Route::delete('clientes/{id}/documentos/{oid}', 'ClienteAPIController@destroyDocumentos');
    Route::put('clientes/{id}/documentos/{oid}', 'ClienteAPIController@updateDocumentos');
    Route::post('clientes/{id}/documentos', 'ClienteAPIController@storeDocumentos');    
    Route::get('clientes/{id}/documentos', 'ClienteAPIController@getDocumentos');


    Route::delete('clientes/{id}/observaciones/{oid}', 'ClienteAPIController@destroyObservaciones');
    Route::put('clientes/{id}/observaciones/{oid}', 'ClienteAPIController@updateObservaciones');
    Route::post('clientes/{id}/observaciones', 'ClienteAPIController@storeObservaciones');    
    Route::get('clientes/{id}/observaciones', 'ClienteAPIController@getObservaciones');

    Route::delete('clientes/{id}/avisos/{oid}', 'ClienteAPIController@destroyAvisos');
    Route::put('clientes/{id}/avisos/descartar/{aid}', 'ClienteAPIController@descartarAvisos');
    Route::put('clientes/{id}/avisos/{oid}', 'ClienteAPIController@updateAvisos');
    Route::post('clientes/{id}/avisos', 'ClienteAPIController@storeAvisos');    
    Route::get('clientes/{id}/avisos', 'ClienteAPIController@getAvisos');

    
    Route::delete('clientes/{id}/requerimientos/{rid}', 'ClienteAPIController@destroyRequerimientos');
    Route::put('clientes/{id}/requerimientos/asignar-tramite/{rid}', 'ClienteAPIController@asignarTramiteRequerimientos');
    Route::put('clientes/{id}/requerimientos/{rid}', 'ClienteAPIController@updateRequerimientos');
    Route::post('clientes/{id}/requerimientos', 'ClienteAPIController@storeRequerimientos');    
    Route::get('clientes/{id}/requerimientos/{rid}/enviar-doc-email', 'ClienteAPIController@enviarDocRequerimiento');
    Route::get('clientes/{id}/requerimientos', 'ClienteAPIController@getRequerimientos');

    Route::delete('clientes/{id}/tramites/{tid}/beneficios/{benid}', 'TramiteClienteAPIController@destroyBeneficios');
    Route::get('clientes/{id}/tramites/{tid}/beneficios', 'TramiteClienteAPIController@getBeneficios');
    Route::post('clientes/{id}/tramites/{tid}/beneficios', 'TramiteClienteAPIController@storeBeneficios');    
    Route::put('clientes/{id}/tramites/{tid}/beneficios/{benid}', 'TramiteClienteAPIController@updateBeneficios');    

    Route::delete('clientes/{id}/tramites/{tid}/conciliacion/{benid}', 'TramiteClienteAPIController@destroyConciliacion');
    Route::get('clientes/{id}/tramites/{tid}/conciliacion', 'TramiteClienteAPIController@getConciliacion');
    Route::post('clientes/{id}/tramites/{tid}/conciliacion', 'TramiteClienteAPIController@storeConciliacion');    
    Route::put('clientes/{id}/tramites/{tid}/conciliacion/{benid}', 'TramiteClienteAPIController@updateConciliacion');

    Route::delete('clientes/{id}/tramites/{tid}', 'ClienteAPIController@destroyTramites');
    Route::put('clientes/{id}/tramites/{tid}', 'ClienteAPIController@updateTramites');
    Route::post('clientes/{id}/tramites', 'ClienteAPIController@storeTramites');    
    Route::get('clientes/{id}/tramites/{tipo?}', 'ClienteAPIController@getTramites');



    
    
    Route::resource('clientes', 'ClienteAPIController');
    Route::resource('tramites', 'TramiteClienteAPIController');

    Route::delete('provincias/eliminar-seleccion', 'ProvinciaAPIController@removeSelected');
    Route::resource('provincias', 'ProvinciaAPIController');   

    Route::delete('paises/eliminar-seleccion', 'PaisAPIController@removeSelected');
    Route::resource('paises', 'PaisAPIController');

    //Route::resource('acciones-controladas', 'AccionesControladasAPIController');

    Route::delete('tipo-documentos/eliminar-seleccion', 'TipoDocumentoAPIController@removeSelected');
    Route::resource('tipo-documentos', 'TipoDocumentoAPIController');

    Route::delete('tipo-aportes/eliminar-seleccion', 'TipoAporteAPIController@removeSelected');
    Route::resource('tipo-aportes', 'TipoAporteAPIController');    

    Route::delete('tipo-tramites/eliminar-seleccion', 'TipoTramiteAPIController@removeSelected');
    Route::resource('tipo-tramites', 'TipoTramiteAPIController');

    Route::delete('tipo-clientes/eliminar-seleccion', 'TipoClienteAPIController@removeSelected');
    Route::resource('tipo-clientes', 'TipoClienteAPIController');

    Route::delete('estado-requerimientos/eliminar-seleccion', 'EstadoRequerimientoAPIController@removeSelected');
    Route::resource('estado-requerimientos', 'EstadoRequerimientoAPIController');   

    Route::delete('estado-anses/eliminar-seleccion', 'EstadoAnsesAPIController@removeSelected');
    Route::resource('estado-anses', 'EstadoAnsesAPIController');

    Route::delete('estado-tramites/eliminar-seleccion', 'EstadoTramiteAPIController@removeSelected');
    Route::resource('estado-tramites', 'EstadoTramiteAPIController');

    Route::delete('tipo-sociedades/eliminar-seleccion', 'TipoSociedadAPIController@removeSelected');
    Route::resource('tipo-sociedades', 'TipoSociedadAPIController');

    Route::delete('cond-iva/eliminar-seleccion', 'CondIvaAPIController@removeSelected');
    Route::resource('cond-iva', 'CondIvaAPIController');

    Route::delete('colegas/eliminar-seleccion', 'ColegaAPIController@removeSelected');
    Route::resource('colegas', 'ColegaAPIController');

    Route::delete('empresas-referencia/eliminar-seleccion', 'EmpresaReferenciaAPIController@removeSelected');
    Route::resource('empresas-referencia', 'EmpresaReferenciaAPIController');   

    Route::delete('reparticiones-origen/eliminar-seleccion', 'ReparticionOrigenAPIController@removeSelected');
    Route::resource('reparticiones-origen', 'ReparticionOrigenAPIController');

    Route::delete('juzgados/eliminar-seleccion', 'JuzgadoAPIController@removeSelected');
    Route::resource('juzgados', 'JuzgadoAPIController');

    Route::delete('estados-expediente/eliminar-seleccion', 'EstadoExpedienteAPIController@removeSelected');
    Route::resource('estados-expediente', 'EstadoExpedienteAPIController');

    Route::delete('doc-requerida/eliminar-seleccion', 'DocRequeridaAPIController@removeSelected');
    Route::resource('doc-requerida', 'DocRequeridaAPIController');

    Route::delete('usuarios/roles/eliminar-seleccion', 'RolesAPIController@removeSelected');
    Route::resource('usuarios/roles', 'RolesAPIController');

    Route::resource('documento-clientes', 'DocumentoClienteAPIController');

    Route::delete('time-gestion/eliminar-seleccion', 'TimeGestionAPIController@removeSelected');
    Route::resource('time-gestion', 'TimeGestionAPIController');    

    Route::delete('time-referencias/eliminar-seleccion', 'TimeReferenciaAPIController@removeSelected');
    Route::resource('time-referencias', 'TimeReferenciaAPIController');

    Route::delete('time-horas/eliminar-seleccion', 'TimeHoraAPIController@removeSelected');
    Route::post('time-horas/facturar', 'TimeHoraAPIController@facturar');
    Route::post('time-horas/filtrar', 'TimeHoraAPIController@index');
    Route::resource('time-horas', 'TimeHoraAPIController');
    Route::post('time-impresiones/cliente-abogado', 'TimeImpresionesAPIController@clienteAbogado');
    Route::post('time-impresiones/abogado-cliente', 'TimeImpresionesAPIController@abogadoCliente');
    Route::post('time-impresiones/abogado-resumen', 'TimeImpresionesAPIController@abogadoResumen');


    Route::delete('usuarios/eliminar-seleccion', 'UserController@removeSelected');
    Route::put('usuarios/password/reset/{id}', 'UserController@resetPassword');
    Route::put('usuarios/update-profile/{id}', 'UserController@updateProfile');

    Route::resource('usuarios', 'UserController');

    Route::get('roles/full','RolesAPIController@full');

    Route::group(['prefix' => 'informes'],function() {
        Route::post('exp-jud','InformesAPIController@expedientesJudiciales');   
        Route::post('beneficios','InformesAPIController@beneficios');    
        Route::post('tramites','InformesAPIController@tramites');    
        Route::post('pensiones','InformesAPIController@pensiones');  
        Route::post('tramites-historicos','InformesAPIController@tramitesHistoricos');      
        Route::post('requerimientos-empresas','InformesAPIController@requerimientosEmpresas');      
        Route::post('ucadep','InformesAPIController@ucadep');      
    });
});


/*Route::delete('requerimiento_clientes/eliminar-seleccion', 'RequerimientoClienteAPIController@removeSelected');
Route::resource('requerimiento_clientes', 'RequerimientoClienteAPIController');

Route::delete('responsable_requerimientos/eliminar-seleccion', 'ResponsableRequerimientoAPIController@removeSelected');
Route::resource('responsable_requerimientos', 'ResponsableRequerimientoAPIController');*/

/*Route::delete('tramite_beneficios/eliminar-seleccion', 'TramiteBeneficioAPIController@removeSelected');
Route::resource('tramite_beneficios', 'TramiteBeneficioAPIController');*/



Route::delete('tramite_expedientes/eliminar-seleccion', 'TramiteExpedienteAPIController@removeSelected');
Route::resource('tramite_expedientes', 'TramiteExpedienteAPIController');



Route::delete('aviso_clientes/eliminar-seleccion', 'AvisoClienteAPIController@removeSelected');
Route::resource('aviso_clientes', 'AvisoClienteAPIController');

Route::delete('vencimientos/eliminar-seleccion', 'VencimientoAPIController@removeSelected');
Route::resource('vencimientos', 'VencimientoAPIController');

Route::delete('time_gestions/eliminar-seleccion', 'TimeGestionAPIController@removeSelected');
Route::resource('time_gestions', 'TimeGestionAPIController');