<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TramiteExpediente
 * @package App\Models
 * @version April 23, 2018, 7:21 pm UTC
 *
 * @property integer tramite_id
 * @property integer area_id
 * @property string nro_expediente
 * @property integer juzgado_id
 * @property date fecha
 * @property integer estado_id
 * @property boolean vuelta_anses
 */
class TramiteExpediente extends Model
{
    use SoftDeletes;

    public $table = 'tramite_expedientes';
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'tramite_id',
        'area_id',
        'nro_expediente',
        'juzgado_id',
        'fecha',
        'estado_id',
        'vuelta_anses',
        'juicio_conciliado',
        'nro_conciliacion',
        'fecha_conciliacion',
        'monto_conciliacion'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'tramite_id' => 'integer',
        'area_id' => 'integer',
        'nro_expediente' => 'string',
        'juzgado_id' => 'integer',
        'fecha' => 'date',
        'estado_id' => 'integer',
        'vuelta_anses' => 'boolean',
        'juicio_conciliado' => 'boolean',
        'nro_conciliacion' => 'string',
        'fecha_conciliacion' => 'date',
        'monto_conciliacion' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'tramite_id' => 'required',
        'area_id' => 'required'
    ];

    public function getFechaAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d',$value)->format('d/m/Y') : "");
    }
    public function setFechaAttribute($value)
    {
        $this->attributes['fecha'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    }    

    public function getFechaConciliacionAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d',$value)->format('d/m/Y') : "");
    }
    public function setFechaConciliacionAttribute($value)
    {
        $this->attributes['fecha_conciliacion'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    }    

    public function juzgado() 
    {
        return $this->belongsTo('App\Models\Juzgado','juzgado_id');
    }      
    public function tramite() 
    {
        return $this->belongsTo('App\Models\TramiteCliente','tramite_id');
    }  
    public function area() 
    {
        return $this->belongsTo('App\Models\Area','area_id');
    }      
}
