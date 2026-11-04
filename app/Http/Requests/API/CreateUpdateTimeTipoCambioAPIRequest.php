<?php

namespace App\Http\Requests\API;

use App\Models\TimeTipoCambio;
use App\Http\Requests\APIRequest;

class CreateUpdateTimeTipoCambioAPIRequest extends APIRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return TimeTipoCambio::$rules;
    }
}
