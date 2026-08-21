<?php

namespace App\Observers;

use App\Models\Cliente;
        
class ClienteObserver
{

    public function deleted(Cliente $model)
    {
        $model->cuit = $model->id . '_' . $model->cuit;
        $model->nro_doc = $model->id . '_' . $model->nro_doc;
        $model->save();
    }
}