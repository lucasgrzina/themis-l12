<?php

namespace App\Observers;

use App\Models\CondIva;
        
class CondIvaObserver
{

    public function deleted(CondIva $model)
    {
        $model->nombre = $model->id . '_' . $model->nombre;
        $model->save();
    }
}