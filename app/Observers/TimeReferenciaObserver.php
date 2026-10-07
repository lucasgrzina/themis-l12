<?php

namespace App\Observers;

use App\Models\TimeGestion;
        
class TimeReferenciaObserver
{
    public function deleted(TimeGestion $model)
    {
        $model->nombre = $model->id . '_' . $model->nombre;
        $model->save();
    }
}