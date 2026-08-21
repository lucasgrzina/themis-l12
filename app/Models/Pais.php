<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Pais
 * @package App\Models
 * @version December 21, 2017, 7:39 pm UTC
 *
 * @property string nombre
 */
class Pais extends Model
{
    use SoftDeletes;

    public $table = 'paises';
    

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
        'nombre' => 'required|unique:paises,nombre,{:id},id'
    ];

    
}
