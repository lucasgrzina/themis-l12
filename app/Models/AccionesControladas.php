<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class AccionesControladas
 * @package App\Models
 * @version November 17, 2017, 7:16 pm UTC
 *
 * @property string nombre
 */
class AccionesControladas extends Model
{
    use SoftDeletes;

    public $table = 'acciones_controladas';
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'nombre'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'nombre' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'nombre' => 'required|unique:acciones_controladas,nombre,{:id},id'
    ];

    
}
