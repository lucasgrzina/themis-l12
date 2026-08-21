<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class AvisoCliente
 * @package App\Models
 * @version May 28, 2018, 4:09 pm -03
 *
 * @property integer user_id
 * @property integer cliente_id
 * @property string motivo
 * @property boolean descartado
 * @property string|\Carbon\Carbon fecha
 */
class AvisoCliente extends Model
{
    use SoftDeletes;

    public $table = 'aviso_clientes';
    
    public $appends = ['status'];
    protected $dates = ['deleted_at'];


    public $fillable = [
        'user_id',
        'cliente_id',
        'motivo',
        'descartado',
        'fecha',
        'type_id',
        'type'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'cliente_id' => 'integer',
        'motivo' => 'string',
        'descartado' => 'boolean',
        'type' => 'string',
        'type_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'user_id' => 'required',
        'cliente_id' => 'required',
        'motivo' => 'required',
        'fecha' => 'required'
    ];

    public function getFechaAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$value)->toIso8601String() : null);
    }
    public function setFechaAttribute($value)
    {
        $this->attributes['fecha'] = ($value ? \Carbon\Carbon::createFromFormat('Y-m-d\TH:i:s.uO',$value)->format('Y-m-d H:i:s') : null);
    }    
    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = ($value ? $value : 'U');
    }  

    public function getStatusAttribute($value) 
    {
        if ($this->descartado) 
        {
            return "DESCARTADO";
        }
        $today = \Carbon\Carbon::now();
        $fecha = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$this->attributes['fecha']);
        if ($fecha->gte($today))
        {
            return "PENDIENTE";
        }
        else 
        {
            return "VENCIDO";
        }
    }

    public function cliente() 
    {
        return $this->belongsTo('App\Models\Cliente','cliente_id');
    }     
    
    public function user() 
    {
        return $this->belongsTo('App\User','user_id');
    }        
    
}
