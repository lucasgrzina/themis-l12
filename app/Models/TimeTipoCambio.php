<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class TimeTipoCambio
 * @package App\Models
 *
 * Tipo de cambio mensual (manual) para el informe de liquidacion.
 *
 * @property date periodo
 * @property string nombre
 * @property float valor
 */
class TimeTipoCambio extends Model
{
    public $table = 'time_tipos_cambio';

    protected $dates = ['periodo'];

    public $fillable = [
        'periodo',
        'nombre',
        'valor',
    ];

    protected $casts = [
        'nombre' => 'string',
        'valor' => 'decimal:4',
    ];

    public static $rules = [
        'periodo' => 'required|date',
        'valor' => 'required|numeric',
    ];
}
