<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Exige el permiso "<modulo>:<operacion>" segun el verbo HTTP:
 * GET/HEAD => R, POST => C, PUT/PATCH => U, DELETE => D.
 * Uso en rutas: ->middleware('permiso.crud:time-parametros')
 */
class PermisoCrud
{
    private const OPERACION = [
        'GET' => 'R', 'HEAD' => 'R',
        'POST' => 'C',
        'PUT' => 'U', 'PATCH' => 'U',
        'DELETE' => 'D',
    ];

    public function handle(Request $request, Closure $next, string $modulo)
    {
        $op = self::OPERACION[$request->method()] ?? null;
        $user = $request->user();

        abort_unless($op && $user && $user->can("$modulo:$op"), 403, 'No tiene permisos para realizar esta operación');

        return $next($request);
    }
}
