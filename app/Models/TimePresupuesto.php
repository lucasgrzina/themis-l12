<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TimePresupuesto
 * @package App\Models
 *
 * Presupuesto / abono fijo mensual para clientes con modelo 'presupuesto'.
 *
 * @property integer cliente_id
 * @property date periodo
 * @property string descripcion
 * @property float subtotal
 * @property boolean aplica_iva
 */
class TimePresupuesto extends Model
{
    use SoftDeletes;

    public $table = 'time_presupuestos';

    protected $dates = ['deleted_at', 'periodo'];

    public $fillable = [
        'cliente_id',
        'periodo',
        'descripcion',
        'subtotal',
        'aplica_iva',
    ];

    protected $casts = [
        'cliente_id' => 'integer',
        'descripcion' => 'string',
        'subtotal' => 'decimal:2',
        'aplica_iva' => 'boolean',
    ];

    public static $rules = [
        'cliente_id' => 'required',
        'periodo' => 'required|date',
        'subtotal' => 'required|numeric',
    ];

    public function cliente()
    {
        return $this->belongsTo('App\Models\Cliente', 'cliente_id');
    }
}
