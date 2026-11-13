<?php

namespace App\Services;

/**
 * Permisos (por rol, ABM de Roles) de los informes y parametros de Time.
 * Reemplazan al antiguo control por time_level en estas pantallas.
 */
class TimePermisos
{
    const ANALISIS    = 'time-analisis-de-horas:R';
    const LIQUIDACION = 'time-liquidacion:R';

    /** Corta con 403 si el usuario no tiene el permiso. */
    public static function exigir($user, string $permiso)
    {
        abort_unless($user && $user->can($permiso), 403, 'No tiene permisos para ver este informe');
    }
}
