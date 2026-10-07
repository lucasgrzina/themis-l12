<?php

namespace App\Observers;

use App\Models\Pais;
        
class PaisObserver
{

    public function deleted(Pais $model)
    {
        $model->nombre = $model->id . '_' . $model->nombre;
        $model->save();
    }
}