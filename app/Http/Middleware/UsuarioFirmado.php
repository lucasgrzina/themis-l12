<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Las impresiones/exportaciones web se abren en otra ventana sin sesion ni JWT.
 * El front obtiene antes una URL firmada (TimeImpresionesAPIController@urlFirmada)
 * que lleva el `uid` de quien la pidio dentro de la firma. Este middleware
 * (se usa despues de `signed:relative`) toma ese usuario para que apliquen los
 * mismos permisos de Time que en la API.
 */
class UsuarioFirmado
{
    public function handle(Request $request, Closure $next)
    {
        $user = User::find((int) $request->query('uid'));
        abort_unless($user, 403, 'Enlace inválido');

        Auth::setUser($user);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
