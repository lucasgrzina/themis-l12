<?php

namespace App\Http\Requests\API;

use App\Models\TimeParticipacion;
use App\Http\Requests\APIRequest;

class CreateUpdateTimeParticipacionAPIRequest extends APIRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return TimeParticipacion::$rules;
    }
}
