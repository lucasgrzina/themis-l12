<?php

namespace App\Observers;

use App\Models\ReparticionOrigen;
        
class ReparticionOrigenObserver
{

    public function deleted(ReparticionOrigen $model)
    {
        $model->nombre = $model->id . '_' . $model->nombre;
        $model->save();
    }
}