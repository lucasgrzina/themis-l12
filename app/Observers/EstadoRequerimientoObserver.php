<?php

namespace App\Observers;

use App\Models\EstadoRequerimiento;
        
class EstadoRequerimientoObserver
{

    public function deleted(EstadoRequerimiento $model)
    {
        $model->nombre = $model->id . '_' . $model->nombre;
        $model->save();
    }
}