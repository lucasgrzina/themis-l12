<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ResponsableRequerimiento
 * @package App\Models
 * @version March 19, 2018, 3:20 pm UTC
 *
 * @property integer requerimiento_id
 * @property integer user_id
 * @property date fecha_asignacion
 */
class ResponsableRequerimiento extends Model
{
    use SoftDeletes;

    public $table = 'responsable_requerimientos';
    

    protected $dates = ['deleted_at','fecha_asignacion'];


    public $fillable = [
        'requerimiento_id',
        'user_id',
        'fecha_asignacion',
        'ppal'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'requerimiento_id' => 'integer',
        'user_id' => 'integer',
        'fecha_asignacion' => 'date',
        'ppal' => 'boolean'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'requerimiento_id' => 'required',
        'user_id' => 'required',
        //'fecha_asignacion' => 'required'

    ];

    public function getFechaAsignacionAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$value)->format('d/m/Y') : "");
    }
    public function setFechaAsignacionAttribute($value)
    {
        $this->attributes['fecha_asignacion'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    }      

    public function user() 
    {
        return $this->belongsTo('App\User','user_id');
    }      

}
