<?php

namespace App\Http\Requests\API;

use App\Models\Cliente;
use App\Http\Requests\APIRequest;

class CreateUpdateClienteAPIRequest extends APIRequest
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
        $rules = Cliente::$rules;

        if ($this->get('cuit','')) {
            $rules['cuit'] = str_replace('{:id}',$this->get('id'),$rules['cuit']);     
        } else {
            unset ($rules['cuit']);
        }

        //\Log::info($rules);
        return $rules;
    }
}
