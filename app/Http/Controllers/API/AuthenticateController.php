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

        // Bloquea el acceso de usuarios dados de baja (activo = false): se valida
        // despues de la clave para no revelar el estado a quien no la conoce.
        $user = JWTAuth::user();
        if ($user && ! $user->activo) {
            JWTAuth::invalidate($token);
            return response()->json(['error' => 'El usuario se encuentra inactivo. Contacte al administrador.'], 401);
        }

        return response()->json(compact('token'));
    }

    /**
     * Renueva el token JWT a partir del token actual (valido o expirado dentro
     * de refresh_ttl). Usado por la webapp de carga para mantener la sesion
     * sin obligar a reloguear cada hora.
     */
    public function refresh()
    {
        try {
            // Si el usuario fue dado de baja, no se le renueva la sesion.
            $user = JWTAuth::parseToken()->authenticate();
            if ($user && ! $user->activo) {
                JWTAuth::invalidate(JWTAuth::getToken());
                return response()->json(['error' => 'El usuario se encuentra inactivo.'], 401);
            }

            $token = JWTAuth::parseToken()->refresh();
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not refresh token'], 401);
        }

        return response()->json(compact('token'));
    }
}