<?php

namespace App\Observers;

use App\Models\Area;
        
class AreaObserver
{

    public function deleted(Area $model)
    {
        $model->nombre = $model->id . '_' . $model->nombre;
        $model->save();
    }
}