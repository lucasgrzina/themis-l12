<?php

namespace App\Http\Requests\API;

use App\Models\TimeParticipacionExterna;
use App\Http\Requests\APIRequest;

class CreateUpdateTimeParticipacionExternaAPIRequest extends APIRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return TimeParticipacionExterna::$rules;
    }
}
