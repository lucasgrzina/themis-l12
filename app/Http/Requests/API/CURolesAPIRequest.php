<?php

namespace App\Http\Requests\API;

use Spatie\Permission\Models\Role;
use App\Http\Requests\APIRequest;

class CURolesAPIRequest extends APIRequest
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
        $rules = [
            'name' => 'required|unique:roles,name,'.$this->get('id').',id'
        ];
        return $rules;
    }
}
