<?php

namespace App\Http\Requests\API;

use App\Models\TimePresupuesto;
use App\Http\Requests\APIRequest;

class CreateUpdateTimePresupuestoAPIRequest extends APIRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return TimePresupuesto::$rules;
    }
}
