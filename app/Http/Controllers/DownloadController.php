<?php

namespace App\Http\Controllers;

use App\Repositories\DocumentoClienteRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function docCliente($id,DocumentoClienteRepository $repo)
    {
    	$model = $repo->findWithoutFail($id);
		$file = Storage::disk('uploads')->get('docs/'.$model->nombre_archivo);
    	return response()->download(Storage::disk('uploads')->getDriver()->getAdapter()->applyPathPrefix('docs/'.$model->nombre_archivo),$model->nombre_real);
    }
}
