<?php

namespace App\Http\Requests\API;

use App\Models\EstadoTramite;
use App\Http\Requests\APIRequest;

class CreateUpdateEstadoTramiteAPIRequest extends APIRequest
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
        $rules = EstadoTramite::$rules;
        $rules['nombre'] = str_replace('{:id}',$this->get('id'),$rules['nombre']); 
        $rules['nombre'] = str_replace('{:area_id}',$this->get('area_id'),$rules['nombre']); 
        return $rules;
    }
}
