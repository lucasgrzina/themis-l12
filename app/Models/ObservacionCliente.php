<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ObservacionCliente
 * @package App\Models
 * @version March 15, 2018, 4:36 pm UTC
 *
 * @property integer usuario_id
 * @property integer user_id
 * @property integer cliente_id
 * @property string observacion
 */
class ObservacionCliente extends Model
{
    use SoftDeletes;

    public $table = 'observacion_clientes';
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'user_id',
        'cliente_id',
        'observacion'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'cliente_id' => 'integer',
        'observacion' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        //'user_id' => 'required',
        'cliente_id' => 'required',
        'observacion' => 'required'
    ];

    public function getCreatedAtAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$value)->format('d/m/Y') : null);
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
