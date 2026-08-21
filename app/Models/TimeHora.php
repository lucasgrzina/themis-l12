<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TimeHora
 * @package App\Models
 * @version November 29, 2018, 5:10 pm -03
 *
 * @property integer user_id
 * @property integer cliente_id
 * @property integer gestion_id
 * @property integer referencia_id
 * @property string referencia
 * @property boolean facturar
 * @property string descripcion
 * @property date fecha
 * @property integer minutos
 */
class TimeHora extends Model
{
    use SoftDeletes;

    public $table = 'time_horas';
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'user_id',
        'cliente_id',
        'gestion_id',
        'referencia',
        'facturar',
        'descripcion',
        'fecha',
        'minutos'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'cliente_id' => 'integer',
        'gestion_id' => 'integer',
        'referencia' => 'string',
        'facturar' => 'boolean',
        'descripcion' => 'string',
        'fecha' => 'date',
        'minutos' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        //'user_id' => 'required',
        'cliente_id' => 'required',
        'gestion_id' => 'required',
        //'referencia_id' => 'required_if:referencia,==,null',
        //'fecha' => 'required',
        'minutos' => 'required'
    ];

    public $appends = ['fecha_dp'];

    public function getFechaDpAttribute($value)
    {
        return ($this->attributes['fecha'] ? \Carbon\Carbon::createFromFormat('Y-m-d',\Illuminate\Support\Str::limit($this->attributes['fecha'],10,''))->toIso8601String() : "");
    }

    public function getFechaAttribute($value)
    {
        return ($this->attributes['fecha'] ? \Illuminate\Support\Str::limit($this->attributes['fecha'],10,'') : null);
    }

    public function cliente() 
    {
        return $this->belongsTo('App\Models\Cliente','cliente_id');
    } 

    public function usuario() 
    {
        return $this->belongsTo('App\User','user_id');
    } 
        
    public function gestion() 
    {
        return $this->belongsTo('App\Models\TimeGestion','gestion_id');
    } 
     
}
