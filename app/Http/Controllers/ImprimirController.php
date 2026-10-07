<?php

namespace App\Http\Controllers;


use App\Exports\InformeTramites;
use App\Http\Controllers\Traits\GenerarDocumentacionTrait;
use App\Http\Controllers\informeTramitesHistoricos;
use App\Models\Area;
use App\Models\Cliente;
use App\Models\Colega;
use App\Models\EstadoAnses;
use App\Models\EstadoRequerimiento;
use App\Models\EstadoTramite;
use App\Models\ReparticionOrigen;
use App\Models\TipoTramite;
use App\Repositories\ClienteRepository;
use App\Repositories\InformesRepository;
use App\Repositories\RequerimientoClienteRepository;
use App\Repositories\TimeHoraRepository;
use App\User;
use Illuminate\Http\Request;

class ImprimirController extends Controller
{
	use GenerarDocumentacionTrait;
	protected $tableStyle = 'border-spacing: 0;border-collapse: collapse;width: 100%; border-color:#999999;';
    protected $tdBodyStyle = 'border-style: solid;border-left-style: none;border-right-style: none;border-width: 0 0 1px 0;';
    protected $tdHeadStyle = 'border-style: solid;border-top-style: none;border-left-style: none;border-right-style: none;border-width: 0px 0px 3px 0px;';
    protected $textUnderline = 'text-decoration: underline;';


    protected function setHeaders($fileName)
    {
			$headers = array(
				"Content-type"=>"text/html",
				"charset" => "utf-8",
				"Content-Disposition"=>"attachment;Filename={$fileName}"
			);

			return $headers;    	
    }
    
    public function documentacionReq($idRequerimiento, RequerimientoClienteRepository $reqRepo) 
    {
    	
		try
		{
	        $model = $reqRepo->with('responsables.user','cliente')->findWithoutFail($idRequerimiento);

	        if (empty($model)) {
	            return $this->sendError(trans('api.not_found'));
	        }

		    $contents = $this->getContenidoPorArea($model);

            $pdf = \PDF::loadHtml($contents);

            return $pdf->stream("dompdf_out.pdf", array("Attachment" => false));
            exit(0);

		    //return $contents;
		    /*$docWord = $this->nuevoWord();

			\PhpOffice\PhpWord\Shared\Html::addHtml($docWord->getSections()[0], $contents, false, false);

			$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($docWord, 'Word2007');

			$nombreDoc = "Documentacion-Req-{$model->id}-" . str_replace(' ','-',$model->tipoTramite->nombre).".docx";

			$objWriter->save(storage_path($nombreDoc));

	        return response()->download(storage_path($nombreDoc));
            */

		}
		catch (Illuminate\Filesystem\FileNotFoundException $exception)
		{
		    die("The file doesn't exist");
		}    	
    }

    public function timeImpAbogadoResumen(Request $request,TimeHoraRepository $timeRepo)
    {
        try
        {
            $data = $timeRepo->impresionesAbogadoResumen($request,false);
            $data['filtros'] = ['desde' => $request->get('desde'),'hasta' => $request->get('hasta')];
            $data['filtros']['cliente'] = Cliente::find($request->get('cliente_id'))->nombre_completo; 
            $contents = view('time.abogado-resumen',$data)->render();

            $pdf = \PDF::loadHtml($contents);

            return $pdf->stream("dompdf_out.pdf", array("Attachment" => false));
            exit(0);                
        }
        catch (\Exception $e)
        {
            die($e->getMessage());
        }
        
    }

    public function timeImpClienteAbogado(Request $request,TimeHoraRepository $timeRepo)
    {
        try
        {
            $data = $timeRepo->impresionesClienteAbogado($request,false);
            $data['filtros'] = ['desde' => $request->get('desde'),'hasta' => $request->get('hasta')];
            $data['filtros']['cliente'] = Cliente::find($request->get('cliente_id'))->nombre_completo; 
            $contents = view('time.cliente-abogado',$data)->render();
			
            $pdf = \PDF::loadHtml($contents);
			
            return $pdf->stream("dompdf_out.pdf", array("Attachment" => false));
            exit(0);                
        }
        catch (\Exception $e)
        {
            die($e->getMessage());
        }
        
    }

    public function timeImpAbogadoCliente(Request $request,TimeHoraRepository $timeRepo)
    {
        try
        {
            $data = $timeRepo->impresionesAbogadoCliente($request,false);
            $data['filtros'] = ['desde' => $request->get('desde'),'hasta' => $request->get('hasta')];
            $user_id = $request->get('user_id');
            $data['filtros']['abogado'] = $user_id ? optional(User::find($user_id))->name : 'Todos';
            $contents = view('time.abogado-cliente',$data)->render();

            $pdf = \PDF::loadHtml($contents);

            return $pdf->stream("dompdf_out.pdf", array("Attachment" => false));
            exit(0);                
        }
        catch (\Exception $e)
        {
            die($e->getMessage());
        }
        
    }

    public function informeExpJud(Request $request,InformesRepository $informesRepo)
    {

		try
		{
	        $data = $informesRepo->expedientesJudiciales($request);
			return \Excel::download(new \App\Exports\InformeExpJud($data,$request->all(),$this->getFiltersInfo('exp-jud',$request)),'exp-jud.xlsx');
		}
		catch (\Exception $e)
		{
		    die($e->getMessage());
		}      	
    }

    public function informeUcadep(Request $request,InformesRepository $informesRepo)
    {
		try
		{
	        $data = $informesRepo->ucadep($request);
			return \Excel::download(new \App\Exports\InformeUcadep($data,$request->all(),$this->getFiltersInfo('ucadep',$request)),'ucadep.xlsx');
		}
		catch (Illuminate\Filesystem\FileNotFoundException $exception)
		{
		    die("The file doesn't exist");
		}      	
    }

    public function informeBeneficios(Request $request,InformesRepository $informesRepo)
    {

		try
		{
	        $data = $informesRepo->beneficios($request);
	        return \Excel::download(new \App\Exports\InformeBeneficios($data,$request->all(),$this->getFiltersInfo('beneficios',$request)),'beneficios.xlsx');
		}
		catch (Illuminate\Filesystem\FileNotFoundException $exception)
		{
		    die("The file doesn't exist");
		}      	
    }

    public function informeRequerimientosEmpresas(Request $request,InformesRepository $informesRepo)
    {
		try
		{
	        $data = $informesRepo->requerimientosEmpresas($request);
			return \Excel::download(new \App\Exports\InformeRequerimientosEmpresas($data,$request->all(),$this->getFiltersInfo('req-emp',$request)),'requerimientos.xlsx');
		}
		catch (Illuminate\Filesystem\FileNotFoundException $exception)
		{
		    die("The file doesn't exist");
		}      	
    }

    public function informeTramites(Request $request,InformesRepository $informesRepo)
    {

		try
		{
	        $data = $informesRepo->tramites($request);
			return \Excel::download(
				new \App\Exports\InformeTramites($data,$request->all(),$this->getFiltersInfo('tramites',$request)),
				'tramites.xlsx'
			);
		}
		catch (Illuminate\Filesystem\FileNotFoundException $exception)
		{
		    die("The file doesn't exist");
		}      	
    }

    public function informeTramitesHistoricos(Request $request,InformesRepository $informesRepo)
    {

		try
		{
	        $data = $informesRepo->tramitesHistoricos($request);
	        return \Excel::download(new \App\Exports\informeTramitesHistoricos($data,$request->all(),$this->getFiltersInfo('tramites-historicos',$request)),'tramites-historicos.xlsx');

		    $contents = $this->getFiltersInfo('tramites-historicos',$request);
		    $contents.= "
		    	<table align='center' style='{$this->tableStyle}'>
		    		<thead>
		    			<tr>";

		    			if ($request->get('area_id') == 1) {
		    				$contents.= "
		    					<td style='{$this->tdHeadStyle}'>Expediente</td>
	    					";		    				
		    			}

	    				$contents.= "
	    					<td style='{$this->tdHeadStyle}'>Cliente</td>
	    					<td style='{$this->tdHeadStyle}'>Fecha</td>
	    					<td style='{$this->tdHeadStyle}'>Empresa Ref.</td>
	    					<td style='{$this->tdHeadStyle}'>Abogados Resp.</td>
	    					<td style='{$this->tdHeadStyle}'>".utf8_decode('Tipo Trámite')."</td>
	    					<td style='{$this->tdHeadStyle}'>Fecha Archivo</td>
	    					<td style='{$this->tdHeadStyle}'>Usuario Archivo</td>
						</tr>
    				</thead>
    				<tbody>
	    	";

	    	foreach ($data as $row) {

	    		$contents.= "<tr>";

				if ($request->get('area_id') == 1) {
					$contents.= "
		              	<td style='{$this->tdBodyStyle}'>{$row->expediente}</td>";
				}

				$contents.= "
    				<td style='{$this->tdBodyStyle}'>
		              	<span><strong>".utf8_decode($row->nombre_cliente)."</strong></span><br>
    				</td>
    				<td style='{$this->tdBodyStyle}'>".($row->tramite_f_inicio === null ? '' : \Carbon\Carbon::createFromFormat('Y-m-d',$row->tramite_f_inicio)->format('d/m/Y'))."</td>
					<td style='{$this->tdBodyStyle}'>".utf8_decode($row->empresas_referencia)."</td>
					<td style='{$this->tdBodyStyle}'>".utf8_decode($row->responsables)."</td>
					<td style='{$this->tdBodyStyle}'>".utf8_decode($row->tipo_tramite)."</td>
					<td style='{$this->tdBodyStyle}'>".($row->fecha_archivo === null ? '' : \Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$row->fecha_archivo)->format('d/m/Y'))."</td>
					<td style='{$this->tdBodyStyle}'>".utf8_decode($row->usuario_archivo)."</td>
				</tr>";
	    	}

	    	$contents.= "</tbody></table>";

	    	return \Response::make($contents,200, $this->setHeaders('inf-tramites-historicos.doc'));
	    	//return \Response::make($contents,200);

		}
		catch (Illuminate\Filesystem\FileNotFoundException $exception)
		{
		    die("The file doesn't exist");
		}      	
    }

    protected function getFiltersInfo($informe,Request $request) 
    {
    	$data = [];

    	switch ($informe) {
    		case 'exp-jud':
    			$data = [
    				'Fecha desde:' => $request->get('desde',''),
    				'Fecha hasta:' => $request->get('hasta',''),
    				//'Abogado Resp.:' => $request->get('responsable_id',''),
    				'Area:' => Area::find($request->get('area_id'))->nombre,
    			];
    			break;
    		case 'ucadep':
    			$data = [
                    'Vuelta a Anses Desde:' => $request->get('anses_desde',''),
                    'Vuelta a Anses hasta:' => $request->get('anses_hasta',''),                 
    				'Estado Desde:' => $request->get('desde',''),
    				'Estado hasta:' => $request->get('hasta',''),
    				//'Estado:' => $request->get('responsable_id',''),
    				'Area:' => Area::find($request->get('area_id'))->nombre,
                    'Ultimo estado:' => ($request->get('ultimo_estado','false') != 'false' ? 'SI' : 'NO'),
    			];
    			break;    		
    		case 'beneficios':
    			//$tipoTramite = 
    			$data = [
    				'Fecha Desde:' => $request->get('desde',''),
    				'Fecha hasta:' => $request->get('hasta',''),
    				//'Abogado Resp.:' => $request->get('responsable_id',''),
    				'Area:' => Area::find($request->get('area_id'))->nombre,
    			];
    			break;
    		case 'tramites':
    			//$tipoTramite = 
    			$data = [
    				'Fecha Desde:' => $request->get('desde',''),
    				'Fecha hasta:' => $request->get('hasta',''),
    				//'Abogado Resp.:' => $request->get('responsable_id',''),
    				'Area:' => Area::find($request->get('area_id'))->nombre,
    			];
    			break;      			    			
    		case 'tramites-historicos':
    			//$tipoTramite = 
    			$data = [
    				'Fecha Desde:' => $request->get('desde',''),
    				'Fecha hasta:' => $request->get('hasta',''),
    				//'Abogado Resp.:' => $request->get('responsable_id',''),
    				'Area:' => Area::find($request->get('area_id'))->nombre,
    			];
    			break; 
    		case 'req-emp':
    			$data = [
    				'Fecha Alta desde:' => $request->get('desde',''),
    				'Fecha Alta hasta:' => $request->get('hasta',''),
    				/*'Nombre desde:' => $request->get('nombre_desde',''),
    				'Nombre hasta:' => $request->get('nombre_hasta',''),    				
    				'CUIT:' => $request->get('cuit',''), */   				
    				//'Abogado Resp.:' => $request->get('responsable_id',''),
    				'Area:' => Area::find($request->get('area_id'))->nombre,
    			];
    			break;    			
    	}

    	if ($request->get('colega_id',NULL) != NULL)
    	{
    		$data['Recomendado Por'] = Colega::find($request->get('colega_id'))->nombre;
    	}

    	if ($request->get('categoria',NULL) != NULL)
    	{
    		if ($request->get('categoria','P') == 'P')
    		{
    			$data['Categ. Cliente'] = 'Persona';
    		}
    		else
    		{
    			$data['Categ. Cliente'] = 'Empresa';
    		}
    		
    	}

    	if ($request->get('tipo_tramite_id',NULL) != NULL)
    	{
    		$data['Tipo Trámite'] = TipoTramite::find($request->get('tipo_tramite_id'))->nombre;
    	}

    	if ($request->get('estado_tramite_id',NULL) != NULL)
    	{
    		$data['Estado Trámite'] = EstadoTramite::find($request->get('estado_tramite_id'))->nombre;
    	}

        if ($request->get('estado_req_id',NULL) != NULL)
        {
            $data['Estado Req.'] = EstadoRequerimiento::find($request->get('estado_req_id'))->nombre;
        }

    	if ($request->get('estado_anses_id',0) != 0)
    	{
    		$data['Estado'] = EstadoAnses::find($request->get('estado_anses_id'))->nombre;
    	}

    	if ($request->get('rep_origen_id',NULL) != NULL)
    	{
    		$data['Rep. Origen'] = ReparticionOrigen::find($request->get('rep_origen_id'))->nombre;
    	}

    	if ($request->get('responsable_area','false') != 'false')
    	{
    		if ($request->get('responsable_id','0') != '0')
    		{
    			$data['Abogado Resp.'] = User::find($request->get('responsable_id'))->name;	
    		}
    		else
    		{
    			$data['Abogado Resp.'] = 'Todos';	
    		}
    	}    
    	else
    	{
    		$data['Abogado Resp.'] = User::find($request->get('user_id'))->name;	
    	}	    	

    	return $data;

    	$contents = "<table align='center'>";
		foreach ($data as $key => $value) {
				$contents.= "<tr><td><strong>".utf8_decode($key)."</strong></td><td>".utf8_decode($value)."</td></tr>";
		}
		$contents.= "</table>";

		return $contents."<br>";
    }


    public function observacionesClientes($cid,ClienteRepository $clientesRepo)
    {
    	

		try
		{
	        $model = $clientesRepo->with([
                'observaciones' => function($query) {
                    $query->select('id','observacion','cliente_id','user_id','created_at')->orderBy('id','desc');
                },
                'observaciones.user' => function($query) {
                    $query->select('id','name');
                } 
	        ])->findWithoutFail($cid);


	        $data = $model->observaciones;
	        //\Log::info($data);

		    $contents = "";
		    $contents.= "
		    	<table align='center' style='{$this->tableStyle}'>
		    		<thead>
		    			<tr>
		    				<td style='{$this->tdHeadStyle}'>Fecha</td>
		    				<td style='{$this->tdHeadStyle}'>Usuario</td>
		    				<td style='{$this->tdHeadStyle}'>Observacion</td>
	    				</tr>
    				</thead>
    				<tbody>
	    	";

	    	foreach ($data as $row) {
	    		$contents.= "
	    			<tr>
	    				<td style='{$this->tdBodyStyle}'>{$row->created_at}</td>
	    				<td style='{$this->tdBodyStyle}'>".utf8_decode($row->user->name)."</td>
	    				<td style='{$this->tdBodyStyle}'>".utf8_decode($row->observacion)."</td>
    				</tr>
	    		";
	    	}

	    	$contents.= "</tbody></table>";

	    	return \Response::make($contents,200, $this->setHeaders('observaciones.doc'));

		}
		catch (Illuminate\Filesystem\FileNotFoundException $exception)
		{
		    die("The file doesn't exist");
		}      	
    }


    private function nuevoWord() {
    	$phpWord = new \PhpOffice\PhpWord\PhpWord();
    	
    	$section = $phpWord->addSection([
    		'headerHeight' => 200,
    	]);

    	$header = $section->addHeader();

    	$header->addImage( asset('img/logo-doc.jpg'), array(
    		//'marginTop' => 50,
    		'align' => 'left',
    		'width' => 200,
    	));

	    $table_style = new \PhpOffice\PhpWord\Style\Table;
	    $table_style->setBorderSize('none');
	    $table_style->setUnit(\PhpOffice\PhpWord\Style\Table::WIDTH_PERCENT);
	    $table_style->setWidth(100*50);
	    $table_style->setCellMargin(0);

    	$table = $header->addTable($table_style);
    	$table->addRow(100,['exactHeight' => true])->addCell(5,['bgColor' => '00529e','borderSize' => 'none'])->addText('', array(), ['spaceAfter' => 0]);
    	$table->addRow(100,['exactHeight' => true])->addCell(5,['bgColor' => 'bcbec0','borderSize' => 'none'])->addText('', array(), ['spaceAfter' => 0]);
    	$table->addRow(200,['exactHeight' => true])->addCell(5,['bgColor' => 'ffffff','borderSize' => 'none'])->addText('', array(), ['spaceAfter' => 0]);

    	return $phpWord;
    }
}
