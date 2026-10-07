<?php

namespace App\Observers;

use App\Models\TimeGestion;
        
class TimeGestionObserver
{
    public function deleting(TimeGestion $model)
    {
    	if ($model->horas()->count() > 0)
    	{
    		throw new \Exception("No se puede eliminar el registro ya que se encuentra en uso en carga de horas", 1);
    	}
    }
    public function deleted(TimeGestion $model)
    {
        $model->nombre = $model->id . '_' . $model->nombre;
        $model->save();
    }
}