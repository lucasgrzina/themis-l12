<?php

namespace App\Http\Requests\API;

use App\Models\TimeTarifa;
use App\Http\Requests\APIRequest;

class CreateUpdateTimeTarifaAPIRequest extends APIRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return TimeTarifa::$rules;
    }
}
