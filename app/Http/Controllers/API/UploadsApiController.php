<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FileUploadTrait;
use Illuminate\Http\Request;

class UploadsApiController extends AppBaseController
{
    use FileUploadTrait;
    public function docs(Request $request)
    {
    	$doc = $this->saveFile($request,'file','tmp');
    	return $this->sendResponse(['doc' => $doc], trans('api.success'));
    }
}
