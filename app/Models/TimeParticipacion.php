<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TimeParticipacion
 * @package App\Models
 *
 * Participacion (% de honorarios) de un abogado en un cliente.
 * periodo NULL = default; periodo seteado = override mensual.
 *
 * @property integer cliente_id
 * @property integer user_id
 * @property float porcentaje
 * @property date periodo
 */
class TimeParticipacion extends Model
{
    use SoftDeletes;

    public $table = 'time_participaciones';

    protected $dates = ['deleted_at', 'periodo'];

    public $fillable = [
        'cliente_id',
        'user_id',
        'porcentaje',
        'periodo',
    ];

    protected $casts = [
        'cliente_id' => 'integer',
        'user_id' => 'integer',
        'porcentaje' => 'decimal:4',
    ];

    public static $rules = [
        'cliente_id' => 'required',
        'user_id' => 'required',
        'porcentaje' => 'required|numeric',
    ];

    public function cliente()
    {
        return $this->belongsTo('App\Models\Cliente', 'cliente_id');
    }

    public function usuario()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
