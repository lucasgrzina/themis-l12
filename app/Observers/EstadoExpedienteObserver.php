<?php

namespace App\Observers;

use App\Models\EstadoExpediente;
        
class EstadoExpedienteObserver
{

    public function deleted(EstadoExpediente $model)
    {
        $model->nombre = $model->id . '_' . $model->nombre;
        $model->save();
    }
}