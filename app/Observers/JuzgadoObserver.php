<?php

namespace App\Observers;

use App\Models\Juzgado;
        
class JuzgadoObserver
{

    public function deleted(Juzgado $model)
    {
        $model->nombre = $model->id . '_' . $model->nombre;
        $model->save();
    }
}