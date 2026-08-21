<?php

namespace App\Http\Controllers;

use App\Exports\UcadepTestExport;
use App\Mail\NuevoAviso;
use App\Models\Cliente;
use App\Models\TramiteCliente;
use App\Repositories\InformesRepository;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class TestController extends Controller
{
    public function index(Request $request) {
    	dd($request->user());
    }

    public function sendEmail()
    {
    	$model = AvisoCliente::whereTypeId(587); 
    	Mail::queue(new NuevoAviso($model));
    	return "1";
    }

    public function printWord(Request $request) 
    {
    	$templatesPath  = \FUHelper::path('','templates');

    	$phpWord = new \PhpOffice\PhpWord\PhpWord();
    	
    	$section = $phpWord->addSection([
    		'headerHeight' => 200,
    		/*'marginTop' => 150,
    		'marginLeft' => 150,
    		'marginRight' => 150,
    		'marginBottom' => 150,*/
    	]);

    	$header = $section->addHeader();

    	$header->addImage( asset('img/logo-doc.jpg'), array(
    		'marginTop' => 10,
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


		
		$html = '<h1>Adding element via HTML</h1>';
		$html .= '<p>Some well-formed HTML snippet needs to be used</p>';
		$html .= '<p>With for example <strong>some<sup>1</sup> <em>inline</em> formatting</strong><sub>1</sub></p>';
		$html .= '<p>A link to <a href="http://phpword.readthedocs.io/" style="text-decoration: underline">Read the docs</a></p>';
		$html .= '<p lang="he-IL" style="text-align: right; direction: rtl">היי, זה פסקה מימין לשמאל</p>';
		$html .= '<p style="margin-top: 240pt;">Unordered (bulleted) list:</p>';
		$html .= '<ul><li>Item 1</li><li>Item 2</li><ul><li>Item 2.1</li><li>Item 2.1</li></ul></ul>';
		$html .= '<p style="margin-top: 240pt;">1.5 line height with first line text indent:</p>';
		$html .= '<p style="text-align: justify; text-indent: 70.9pt; line-height: 150%;">Lorem ipsum dolor sit amet, <strong>consectetur adipiscing elit</strong>, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>';
		$html .= '<h2 style="align: center">centered title</h2>';
		$html .= '<p style="margin-top: 240pt;">Ordered (numbered) list:</p>';
		$html .= '<ol>
		                <li><p style="font-weight: bold;">List 1 item 1</p></li>
		                <li>List 1 item 2</li>
		                <ol>
		                    <li>sub list 1</li>
		                    <li>sub list 2</li>
		                </ol>
		                <li>List 1 item 3</li>
		            </ol>
		            <p style="margin-top: 15px;">A second list, numbering should restart</p>
		            <ol>
		                <li>List 2 item 1</li>
		                <li>List 2 item 2</li>
		                <li>
		                    <ol>
		                        <li>sub list 1</li>
		                        <li>sub list 2</li>
		                    </ol>
		                </li>
		                <li>List 2 item 3</li>
		                <ol>
		                    <li>sub list 1, restarts with a</li>
		                    <li>sub list 2</li>
		                </ol>
		            </ol>';
		$html .= '<p style="margin-top: 240pt;">List with formatted content:</p>';
		$html .= '<ul>
		                <li>
		                    <span style="font-family: arial,helvetica,sans-serif;">
		                        <span style="font-size: 16px;">big list item1</span>
		                    </span>
		                </li>
		                <li>
		                    <span style="font-family: arial,helvetica,sans-serif;">
		                        <span style="font-size: 10px; font-weight: bold;">list item2 in bold</span>
		                    </span>
		                </li>
		            </ul>';
		$html .= '<p style="margin-top: 240pt;">A table with formatting:</p>';
		$html .= '<table align="center" style="width: 50%; border: 6px #0000FF double;">
		                <thead>
		                    <tr style="background-color: #FF0000; text-align: center; color: #FFFFFF; font-weight: bold; ">
		                        <th style="width: 50pt">header a</th>
		                        <th style="width: 50">header          b</th>
		                        <th style="background-color: #FFFF00; border-width: 12px"><span style="background-color: #00FF00;">header c</span></th>
		                    </tr>
		                </thead>
		                <tbody>
		                    <tr><td style="border-style: dotted;">1</td><td colspan="2">2</td></tr>
		                    <tr><td>This is <b>bold</b> text</td><td></td><td>6</td></tr>
		                </tbody>
		            </table>';
		$html .= '<p style="margin-top: 240pt;">Table inside another table:</p>';
		$html .= '<table align="center" style="width: 80%; border: 6px #0000FF double;">
		    <tr><td>
		        <table style="width: 100%; border: 4px #FF0000 dotted;">
		            <tr><td>column 1</td><td>column 2</td></tr>
		        </table>
		    </td></tr>
		    <tr><td style="text-align: center;">Cell in parent table</td></tr>
		</table>';
		\PhpOffice\PhpWord\Shared\Html::addHtml($section, $html, false, false);


   	
		$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
		$objWriter->save("GIE.docx");
		header('Pragma: no-cache');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
		header('Content-Disposition: attachment; filename=GIE.docx;');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.filesize('GIE.docx'));
		readfile('GIE.docx');
		unlink('GIE.docx');	
    }

    public function importarUcadep()
    {


		/*$path = \FUHelper::path().'ucadep-procesar.xlsx';
		\Log::info($path);
		$data = Excel::load($path, function($reader) {
		})->get();*/

		//Storage::disk('uploads')->put('ucadep-procesar.json', json_encode($data));
		//File::put(\FUHelper::path().'ucadep-procesar.json',json_encode($data));


		$data = json_decode(Storage::disk('uploads')->get('ucadep-procesar.json'));


		$clientes = [];
		foreach ($data as $row) 
		{
			if ($row->cliente != null && $row->cliente != 'Cliente')
			{
				if(!array_key_exists($row->cliente, $clientes))
				{
					$clientes[$row->cliente] = [];
				}
				$clientes[$row->cliente][] = \Illuminate\Support\Arr::only(((array)$row),['id_requerimiento','id_tramite']); 

			}
		}

		$errores = [];
		try 
		{
			\DB::beginTransaction();
			foreach ($clientes as $key => $cliente) 
			{
				if (count($cliente) != 2)
				{
					$errores[] = [
						'cliente' => $key,
						'row' => $cliente,
						'motivo' => 'Son dist de 2'
					];
				}
				else
				{
					//Busco el primer tramite, con esto voy a tener el req y el cliente
					$tra_ant = TramiteCliente::with([
						'requerimiento','cliente','expJudicial'
					])->find($cliente[0]['id_tramite']);

					$tra_sig = TramiteCliente::with([
						'requerimiento','cliente'
					])->find($cliente[1]['id_tramite']);				

					if ($tra_ant->requerimiento->tipo_tramite_id == 509 && $tra_sig->requerimiento->tipo_tramite_id == 509)
					{
						if (!$tra_ant->tramite_sig_id)
						{
							if ($tra_ant->expJudicial)
							{
								if(!$tra_sig->tramite_ant_id)
								{
									$tra_ant->expJudicial->vuelta_anses = true;
									$tra_ant->expJudicial->nro_expediente = $tra_sig->expediente;
									//$tra_ant->expJudicial->fecha_inicio = $tra_sig->fecha_inicio;
									$tra_ant->tramite_sig_id = $tra_sig->id;
									$tra_ant->archivar = true;

									//\Log::info([$tra_ant->toArray(),$tra_sig->toArray()]);
									$tra_sig->tramite_ant_id = $tra_ant->id;
									$tra_sig->fecha_remision = $tra_sig->fecha_inicio;
									//\Log::info($tra_sig->fecha_inicio);
									$tra_sig->fecha_remision_vto = \Carbon\Carbon::createFromFormat('d/m/Y',$tra_sig->fecha_inicio)->addWeekdays(120)->format('d/m/Y');
									//\Log::info($tra_sig->fecha_remision_vto);
									$tra_sig->save();
									$tra_ant->expJudicial->save();
									$tra_ant->save(); 
								}
								else
								{
									$errores[] = [
										'cliente' => $key,
										'row' => $cliente,
										'motivo' => 'Tiene tramite ant'
									];	
								}
							}
							else
							{
								$errores[] = [
									'cliente' => $key,
									'row' => $cliente,
									'motivo' => 'No tiene exp jud'
								];						
							}

						}
						else
						{

								if(!$tra_sig->tramite_ant_id)
								{
									$errores[] = [
										'cliente' => $key,
										'row' => $cliente,
										'motivo' => 'Tiene tramite sig pero no ant'
									];							

								} 
								else 
								{
									if($tra_ant->tramiteSig->anses)
									{
										\Log::info('Tiene vuelta a anses');
									}
									else
									{
										$errores[] = [
											'cliente' => $key,
											'row' => $cliente,
											'motivo' => 'Tiene tramite sig y ant, pero el tramite sig no tiene vuelta a anses'
										];									
									}
								}

						}
					}
					else
					{
						$errores[] = [
							'cliente' => $key,
							'row' => $cliente,
							'motivo' => 'Uno de los tramites no es reajuste'
						];
					}
				}
			}
			\DB::commit();

		}
		catch(\Exception $e) 
		{
			\Log::info($e->getMessage());
			\DB::rollback();
		}
		return response()->json($errores);
    }

    public function ucadep()
    {
    	/*Obtengo los tramites que:
		
		1) Tipo tramite: Reajuste mob haberes
		2) No archivados
		3) Previsional
		4) Rep Origen


		*/
		$ucadep = \DB::select("SELECT * 
			FROM tramite_clientes t
			INNER JOIN requerimiento_clientes r ON t.requerimiento_id = r.id

			WHERE t.area_id = 1
			AND t.rep_origen_id = 746
			AND estado_tramite_id = 55
			AND r.tipo_tramite_id = 509
			AND t.fecha_inicio BETWEEN '2000-01-01' AND '2016-11-09'
			AND t.archivar = 0"); 

		$tramitesCliente = [];
		$tienenOtroTramite = [];
		$unSoloTramite = [];

		foreach ($ucadep as $tramite) 
		{
			
			$tramitesMHCliente = TramiteCliente::with([
						'cliente' => function($q) {
							$q->select('id','nombre_completo');
						},
						'estado' => function($q) {
							$q->select('id','nombre');
						}])
			            ->whereClienteId($tramite->cliente_id)
						->whereHas('requerimiento',function($query) {
							$query->whereTipoTramiteId(509);
						})
						//->whereArchivar(true)
						->select('id','requerimiento_id','estado_tramite_id','fecha_inicio','archivar','expediente','cliente_id')
						->orderBy('id')
						->get();

			foreach ($tramitesMHCliente as $row) {
				$tramitesCliente[] = $row;
			}

		}

		//return view('exports.ucadep',['data' => $tramitesCliente]);
		return Excel::download(new UcadepTestExport($tramitesCliente), 'tramites.xlsx');

		//return response()->json($tramitesCliente);
    }

    public function actualizarFechaRemUcadep(InformesRepository $repo)
    {
        $with = [
            'anses' => function($q) {
                $q->orderBy('fecha_remision','asc')->orderBy('id','asc');
            },
            'anses.estado' => function($q) {
                $q->select('id','nombre');
            }/*,     
            'ultimoEstadioAnses.estado' => function($q) {
                $q->select('id','nombre');
            }*/,
            'cliente' => function($q) {
                $q->select('id','personeria','nombre_completo','nombre_conyuge','apellido_conyuge','tipo_doc_conyuge_id','nro_doc_conyuge','cuit');
            },   
            'tramiteAnt'
        ];
        $query = (new TramiteCliente())->newQuery()->with($with)->whereHas('anses')->where('archivar',false)->whereNull('fecha_remision');

        $ids = $query->pluck('id');

        foreach ($ids as $id) 
        {
        	$model = TramiteCliente::find($id);
        	$model->fecha_remision = $model->fecha_inicio;
        	$model->fecha_remision_vto = \Carbon\Carbon::createFromFormat('d/m/Y',$model->fecha_inicio)->addWeekdays(120)->format('d/m/Y');
        	$model->save();
        }


            //\Log::info(array_pluck($data,'id'));
    	/*$tramite = TramiteCliente::with('cliente','requerimiento.tipoTramite','estado','tramiteAnt.estado')->whereNotNull('tramite_ant_id')
    	->whereHas('requerimiento',function($q) {
    		$q->whereTipoTramiteId(509);
    	})
    	->whereHas('tramiteAnt',function($q) {
    		$q->whereEstadoTramiteId(745);
    	})
    	->first();*/


    	return response()->json(1);
    }
}
