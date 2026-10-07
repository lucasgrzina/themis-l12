<?php

namespace App\Http\Requests\API;

use App\Models\TipoSociedad;
use App\Http\Requests\APIRequest;

class CreateUpdateTipoSociedadAPIRequest extends APIRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = TipoSociedad::$rules;
        $rules['nombre'] = str_replace('{:id}',$this->get('id'),$rules['nombre']); 
        return $rules;
    }
}
