<?php

namespace App\Observers;

use App\Models\DocumentoCliente;
use Illuminate\Support\Facades\Storage;
        
class DocumentoClienteObserver
{

    public function creating(DocumentoCliente $model)
    {
    	if (Storage::disk('uploads')->exists('tmp/'.$model->nombre_archivo))
		{
			Storage::disk('uploads')->move('tmp/'.$model->nombre_archivo,'docs/'.$model->nombre_archivo);
		}
    	
    	return true;
    }

    public function updating(DocumentoCliente $model)
    {
    	if (Storage::disk('uploads')->exists('tmp/'.$model->nombre_archivo))
		{
			Storage::disk('uploads')->move('tmp/'.$model->nombre_archivo,'docs/'.$model->nombre_archivo);
		}
    	
    	return true;
    }

    public function updated(DocumentoCliente $model)
    {

		$dirty = $model->getDirty();    	
		$field = 'nombre_archivo';

		if (isset($dirty[$field]))
		{
            if ($model->getOriginal($field) != $dirty[$field])
            {
            	Storage::disk('uploads')->delete('docs/'.$model->getOriginal($field));
            }
		}
		return true;
    }

}