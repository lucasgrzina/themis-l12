<?php

namespace App\Http\Requests\API;

use App\Models\TimeClienteParametro;
use App\Http\Requests\APIRequest;

class CreateUpdateTimeClienteParametroAPIRequest extends APIRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return TimeClienteParametro::$rules;
    }
}
