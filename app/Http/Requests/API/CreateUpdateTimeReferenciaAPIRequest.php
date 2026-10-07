<?php

namespace App\Http\Requests\API;

use App\Models\TimeReferencia;
use App\Http\Requests\APIRequest;

class CreateUpdateTimeReferenciaAPIRequest extends APIRequest
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
        $rules = TimeReferencia::$rules;
        $rules['nombre'] = str_replace('{:id}',$this->get('id'),$rules['nombre']); 
        $rules['nombre'] = str_replace('{:cliente_id}',$this->get('cliente_id'),$rules['nombre']); 
        return $rules;
    }
}
