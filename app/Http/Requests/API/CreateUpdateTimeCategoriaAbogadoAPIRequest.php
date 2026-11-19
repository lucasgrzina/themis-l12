<?php

namespace App\Http\Requests\API;

use App\Models\TimeCategoriaAbogado;
use App\Http\Requests\APIRequest;

class CreateUpdateTimeCategoriaAbogadoAPIRequest extends APIRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = TimeCategoriaAbogado::$rules;
        $rules['nombre'] = str_replace('{:id}', $this->get('id', 'NULL'), $rules['nombre']);
        return $rules;
    }
}
