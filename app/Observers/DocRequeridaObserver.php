<?php

namespace App\Observers;

use App\Models\DocRequerida;
        
class DocRequeridaObserver
{

    public function deleted(DocRequerida $model)
    {
        $model->nombre = $model->id . '_' . $model->nombre;
        $model->save();
    }
}