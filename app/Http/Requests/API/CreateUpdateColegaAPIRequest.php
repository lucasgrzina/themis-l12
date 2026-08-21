<?php

namespace App\Http\Requests\API;

use App\Models\Colega;
use App\Http\Requests\APIRequest;

class CreateUpdateColegaAPIRequest extends APIRequest
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
        $rules = Colega::$rules;
        return $rules;
    }
}
