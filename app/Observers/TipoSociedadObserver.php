<?php

namespace App\Observers;

use App\Models\TipoSociedad;
        
class TipoSociedadObserver
{

    public function deleted(TipoSociedad $model)
    {
        $model->nombre = $model->id . '_' . $model->nombre;
        $model->save();
    }
}