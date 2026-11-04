<?php

namespace App\Http\Requests\API;

use App\Models\TimeRetencion;
use App\Http\Requests\APIRequest;

class CreateUpdateTimeRetencionAPIRequest extends APIRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return TimeRetencion::$rules;
    }
}
