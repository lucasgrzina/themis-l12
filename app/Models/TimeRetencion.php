<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class TimeRetencion
 * @package App\Models
 *
 * Tasa de impuesto / retencion global con vigencia.
 * codigos: imp_cto_dto, sircreb, ret_gcias, iva.
 *
 * @property string codigo
 * @property string nombre
 * @property float tasa
 * @property date vigencia_desde
 */
class TimeRetencion extends Model
{
    public $table = 'time_retenciones';

    protected $dates = ['vigencia_desde'];

    public $fillable = [
        'codigo',
        'nombre',
        'tasa',
        'vigencia_desde',
    ];

    protected $casts = [
        'codigo' => 'string',
        'nombre' => 'string',
        'tasa' => 'decimal:4',
    ];

    public static $rules = [
        'codigo' => 'required',
        'tasa' => 'required|numeric',
    ];
}
