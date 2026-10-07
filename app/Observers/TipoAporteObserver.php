<?php

namespace App\Observers;

use App\Models\TipoAporte;
        
class TipoAporteObserver
{

    public function deleted(TipoAporte $model)
    {
        $model->nombre = $model->id . '_' . $model->nombre;
        $model->save();
    }
}