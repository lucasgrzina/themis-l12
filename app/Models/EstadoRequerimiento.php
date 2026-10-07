<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class EstadoRequerimiento
 * @package App\Models
 * @version December 26, 2017, 4:13 pm UTC
 *
 * @property string nombre
 * @property boolean vigente
 */
class EstadoRequerimiento extends Model
{
    use SoftDeletes;

    public $table = 'estado_requerimientos';
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'nombre',
        'vigente',
        'area_id',
        'modificable'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'nombre' => 'string',
        'vigente' => 'boolean',
        'area_id' => 'integer',
        'modificable' => 'boolean'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'nombre' => 'required|unique:estado_requerimientos,nombre,{:id},id,area_id,{:area_id}',
        //'area_id' => 'required'
    ];

    public function area() 
    {
        return $this->belongsTo('App\Models\Area','area_id');
    } 
}
