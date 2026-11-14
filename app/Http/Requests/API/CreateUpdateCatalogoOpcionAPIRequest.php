<?php

namespace App\Http\Requests\API;

use App\Models\CatalogoOpcion;
use App\Http\Requests\APIRequest;

class CreateUpdateCatalogoOpcionAPIRequest extends APIRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return CatalogoOpcion::rules();
    }
}
