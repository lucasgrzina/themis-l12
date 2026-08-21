<?php

namespace App\Observers;

use App\Models\TipoTramite;
        
class TipoTramiteObserver
{

    public function deleted(TipoTramite $model)
    {
        $model->nombre = $model->id . '_' . $model->nombre;
        $model->save();
    }
}