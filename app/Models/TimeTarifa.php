<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TimeTarifa
 * @package App\Models
 *
 * Valor hora por cliente y categoria de abogado (categoria NULL = tarifa base).
 *
 * @property integer cliente_id
 * @property string categoria
 * @property float valor_hora
 * @property string moneda
 * @property date vigencia_desde
 */
class TimeTarifa extends Model
{
    use SoftDeletes;

    public $table = 'time_tarifas';

    protected $dates = ['deleted_at', 'vigencia_desde'];

    public $fillable = [
        'cliente_id',
        'categoria_id',
        'valor_hora',
        'moneda',
        'vigencia_desde',
    ];

    protected $casts = [
        'cliente_id' => 'integer',
        'categoria_id' => 'integer',
        'valor_hora' => 'decimal:2',
        'moneda' => 'string',
    ];

    public static $rules = [
        'cliente_id' => 'required',
        'valor_hora' => 'required|numeric',
        'categoria_id' => 'nullable|exists:time_categorias_abogado,id',
    ];

    public function categoriaAbogado()
    {
        return $this->belongsTo('App\Models\TimeCategoriaAbogado', 'categoria_id');
    }

    public function cliente()
    {
        return $this->belongsTo('App\Models\Cliente', 'cliente_id');
    }
}
