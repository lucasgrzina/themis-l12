<?php

namespace App\Observers;

use App\Models\EstadoTramite;
        
class EstadoTramiteObserver
{

    public function deleted(EstadoTramite $model)
    {
        $model->nombre = $model->id . '_' . $model->nombre;
        $model->save();
    }
}