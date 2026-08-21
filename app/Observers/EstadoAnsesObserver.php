<?php

namespace App\Observers;

use App\Models\EstadoAnses;
        
class EstadoAnsesObserver
{

    public function deleted(EstadoAnses $model)
    {
        $model->nombre = $model->id . '_' . $model->nombre;
        $model->save();
    }
}