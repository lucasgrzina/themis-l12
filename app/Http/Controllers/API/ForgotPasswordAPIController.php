<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Controller;
use App\Mail\Auth\ForgotPassword;
use App\Repositories\UsersRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordAPIController extends AppBaseController
{
    public function forgot(Request $request,UsersRepository $repo)
    {
        $rules = [
            'username'     =>  'required|exists:users',
        ];

        $this->validate($request, $rules);

        

        try {

        	$model = $repo->findByField('username',$request->get('username'))->first();
        	$newPassword = 'secret';
        	$model->password = Hash::make($newPassword);
        	$model->save();

        	Mail::to($model->email)->send(new ForgotPassword($model,$newPassword));

	        return $this->sendResponse($model->toArray(), 'Se ha enviado una nueva contraseña a ' . $model->email);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }	
}
