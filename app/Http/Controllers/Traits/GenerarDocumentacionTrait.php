<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

trait GenerarDocumentacionTrait
{
    public function getContenidoPorArea($model) 
    {
        $tags = $this->prepararTags();
        $templatesPath  = \FUHelper::path('','templates');
        $area = '';
        switch ($model->area_id) {
            case 1:
                $area = 'previsional';
                break;
            case 2:
                $area = 'laboral';
                break;
            case 3:
                $area = 'civil';
                break;
            case 4:
                $area = 'comercial';
                break;
            case 5:
                $area = 'societario';
                break;
        }

        

        $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        $hoy = \Carbon\Carbon::today();
        $tags['${hoy}'][] = $hoy->format('d') . ' de ' . $meses[$hoy->month - 1] . ' de ' . $hoy->format('Y');
        $tags['${cliente}'][] = 'Sr/a. ' . $model->cliente->nombre_completo;
        $tags['${titulo}'][] = $model->tipoTramite->nombre;

        foreach ($model->documentacion as $doc) {
            switch ($doc->type) {
                case 'A5SP':
                    $tags['${situacion_planteada}'][] = str_replace('<br>','',$doc->value);
                    break;
                case 'A5GH':
                    $tags['${gastos_honorarios}'][] = str_replace('<br>','',$doc->value);
                    break;              
                case 'TT':
                case 'TC':
                    if ($doc->type === 'TC') {
                        if (isset($doc->empresa)) {
                            $ul = "<p><strong>{$doc->empresa}</strong></p>";
                        } else {
                            $ul = "<p><strong>{$doc->tipo_aporte}</strong></p>";
                        }
                    } else {
                        $ul = "";
                    }
                    $ul.= "<div>";
                    foreach ($doc->docs as $value) {
                        //dd($value);
                        if ($model->area_id == 1)
                        {
                            $ul.= "<strong style='text-decoration:underline;'>{$value->nombre}</strong><br>{$value->descripcion}<br>";    
                        }
                        else
                        {
                            $ul.= "<li>{$value->nombre}: {$value->descripcion}</li>";    
                        }
                        
                    }
                    $ul.= "</div>";
                    $tags[($doc->type === 'TT' ? '${doc_tt}' : '${doc_tc}')][] = $ul;
                    break;   
            }
        }

        $responsables = [];

        foreach ($model->responsables as $resp) {
            $responsables[] = "<p>".$resp->user->name."</p>";
        }
        $tags['${responsables}'][] = implode('',$responsables);

        $contents = view('documentacion.doc-' . $area)->render();
        
        foreach ($tags as $key => $content) {
            $contents = str_replace($key, implode('',$content) , $contents);
        }

        

        return $contents;
    }    

    protected function prepararTags() {
        $tags = [
            '${hoy}' => [],
            '${cliente}' => [],
            '${titulo}' => [],
            '${situacion_planteada}' => [],
            '${gastos_honorarios}' => [],
            '${doc_tt}' => [],
            '${doc_tc}' => []
        ];

        return $tags;
    }
}