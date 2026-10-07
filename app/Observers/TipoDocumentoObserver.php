<?php

namespace App\Observers;

use App\Models\TipoDocumento;
        
class TipoDocumentoObserver
{

    public function deleted(TipoDocumento $model)
    {
        $model->nombre = $model->id . '_' . $model->nombre;
        $model->save();
    }
}