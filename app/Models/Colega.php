<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Colega
 * @package App\Models
 * @version December 26, 2017, 7:02 pm UTC
 *
 * @property string nombre
 * @property string direccion
 * @property string localidad
 * @property string telefono
 */
class Colega extends Model
{
    use SoftDeletes;

    public $table = 'colegas';
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'nombre',
        'direccion',
        'localidad',
        'telefono',
        'vigente'
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
        'vigente' => 'boolean'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'nombre' => 'required'
    ];

    
}
