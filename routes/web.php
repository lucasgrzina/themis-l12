<?php
Route::get('test','TestController@index');
Route::get('test/print-word','TestController@printWord');
Route::get('test/ucadep','TestController@ucadep');
Route::get('test/importar-ucadep','TestController@importarUcadep');
Route::get('test/act-ucadep','TestController@actualizarFechaRemUcadep');
Route::get('test/hola',function() {
return 'hola';
});

Route::group(['prefix' => 'mails'],function(){
	Route::get('/',function(){
		return "hola";
	});
	Route::get('/asign-resp-req',function(){
		$req = \App\Models\RequerimientoCliente::with('responsables.user')->find(7763);
		return new \App\Mail\AsignarRespReq($req);
	});
	Route::get('/tramite-iniciado',function(){
		$tra = \App\Models\TramiteCliente::with('cliente')->find(14);
		return new \App\Mail\TramiteIniciado($tra);
	});
	Route::get('/beneficio-acordado',function(){
		$tra = \App\Models\TramiteCliente::with('cliente')->find(36);
		return new \App\Mail\BeneficioAcordado($tra);
	});	

});

Route::group(['prefix' => 'imprimir'],function(){
	Route::get('/','ImprimirController@index')->name('imprimir.index');
	Route::get('/documentacion-req/{id}','ImprimirController@documentacionReq');
	Route::get('/informes/requerimientos-empresas','ImprimirController@informeRequerimientosEmpresas');
	Route::get('/informes/exp-jud','ImprimirController@informeExpJud');
	Route::get('/informes/ucadep','ImprimirController@informeUcadep');
	Route::get('/informes/beneficios','ImprimirController@informeBeneficios');
	Route::get('/informes/tramites','ImprimirController@informeTramites');
	Route::get('/informes/tramites-historicos','ImprimirController@informeTramitesHistoricos');
	Route::get('/observaciones/{id}','ImprimirController@observacionesClientes');
	Route::get('/time/imp-abogado-resumen','ImprimirController@timeImpAbogadoResumen');
	Route::get('/time/imp-cliente-abogado','ImprimirController@timeImpClienteAbogado');
	Route::get('/time/imp-abogado-cliente','ImprimirController@timeImpAbogadoCliente');
});
Route::get('download/doc-cliente/{id}','DownloadController@docCliente')->name('download.doc_cliente');
Route::any('{all}', function () {
    return view('app');
})->where(['all' => '.*']);
