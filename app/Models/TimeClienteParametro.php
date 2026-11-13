<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TimeClienteParametro
 * @package App\Models
 *
 * Parametros de liquidacion por cliente con vigencia (horas director, % director, horas piso).
 *
 * @property integer cliente_id
 * @property date vigencia_desde
 * @property float horas_director
 * @property float pct_director
 * @property float horas_piso
 */
class TimeClienteParametro extends Model
{
    use SoftDeletes;

    public $table = 'time_cliente_parametros';

    protected $dates = ['deleted_at', 'vigencia_desde'];

    public $fillable = [
        'cliente_id',
        'vigencia_desde',
        'horas_director',
        'pct_director',
        'horas_piso',
    ];

    protected $casts = [
        'cliente_id' => 'integer',
        'horas_director' => 'decimal:2',
        'pct_director' => 'decimal:4',
        'horas_piso' => 'decimal:2',
    ];

    public static $rules = [
        'cliente_id' => 'required',
        'horas_director' => 'nullable|numeric|min:0',
        'pct_director' => 'nullable|numeric|min:0|max:1',
        'horas_piso' => 'nullable|numeric|min:0',
    ];
}
