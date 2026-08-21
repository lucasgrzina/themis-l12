<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Controller;
use App\Repositories\AreaRepository;
use Illuminate\Http\Request;

class GeneralDataAPIController extends AppBaseController
{
    public function index(AreaRepository $areasRepo) 
    {
    	$data = [
    		'areas' => $areasRepo->scopeQuery(function($query){
			    return $query->select('id','nombre')->orderBy('nombre','asc');
			})->all()
    	];

    	return $this->sendResponse($data, trans('api.success'));
    }
}
