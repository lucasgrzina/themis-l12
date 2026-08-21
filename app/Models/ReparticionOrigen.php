<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ReparticionOrigen
 * @package App\Models
 * @version December 28, 2017, 1:16 pm UTC
 *
 * @property string nombre
 * @property string direccion
 * @property string localidad
 * @property string telefonos
 * @property boolean vigente
 */
class ReparticionOrigen extends Model
{
    use SoftDeletes;

    public $table = 'reparticion_origen';
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'nombre',
        'direccion',
        'localidad',
        'telefono',
        'vigente',
        'area_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'nombre' => 'string',
        'direccion' => 'string',
        'localidad' => 'string',
        'telefono' => 'string',
        'vigente' => 'boolean',
        'area_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'nombre' => 'required|unique:reparticion_origen,nombre,{:id},id,area_id,{:area_id}'
    ];

    public function area() 
    {
        return $this->belongsTo('App\Models\Area','area_id');
    }      
}
