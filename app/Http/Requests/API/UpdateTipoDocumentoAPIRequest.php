<?php

namespace App\Http\Requests\API;

use App\Models\TipoDocumento;
use App\Http\Requests\APIRequest;

class UpdateTipoDocumentoAPIRequest extends APIRequest
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
        $rules = TipoDocumento::$rules;
        $rules['nombre'] = str_replace('{:id}',$this->get('id'),$rules['nombre']); 
        return $rules;
    }
}
