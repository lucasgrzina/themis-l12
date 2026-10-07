<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
class AuthenticateController extends AppBaseController
{
    public function authenticate(Request $request)
    {
        $rules = [
            'username'     =>  'required',
            'password'  =>  'required'
        ];

        $this->validate($request, $rules);

        $credentials = $request->only('username', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid Login Credential'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        return response()->json(compact('token'));
    }
}