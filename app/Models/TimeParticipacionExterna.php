<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TimeParticipacionExterna
 * @package App\Models
 *
 * Participacion (% de honorarios) de un colaborador EXTERNO (no-usuario) en un
 * cliente con modelo de facturacion 'presupuesto'.
 *
 * base:  'subtotal' (se descuenta antes del subtotal neto)
 *        'subtotal_neto' (se descuenta despues, sobre el neto)
 * periodo NULL = default; periodo seteado = override mensual.
 *
 * @property integer cliente_id
 * @property string nombre
 * @property float porcentaje
 * @property string base
 * @property integer orden
 * @property date periodo
 */
class TimeParticipacionExterna extends Model
{
    use SoftDeletes;

    public $table = 'time_participaciones_externas';

    protected $dates = ['deleted_at', 'periodo'];

    public $fillable = [
        'cliente_id',
        'nombre',
        'porcentaje',
        'base',
        'orden',
        'periodo',
    ];

    protected $casts = [
        'cliente_id' => 'integer',
        'nombre' => 'string',
        'porcentaje' => 'decimal:4',
        'base' => 'string',
        'orden' => 'integer',
    ];

    public static $rules = [
        'cliente_id' => 'required',
        'nombre' => 'required|string|max:150',
        'porcentaje' => 'required|numeric',
        'base' => 'required|in:subtotal,subtotal_neto',
    ];

    public function cliente()
    {
        return $this->belongsTo('App\Models\Cliente', 'cliente_id');
    }
}
