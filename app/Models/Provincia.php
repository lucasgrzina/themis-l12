<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Provincia
 * @package App\Models
 * @version February 27, 2018, 5:33 pm UTC
 *
 * @property string nombre
 */
class Provincia extends Model
{
    use SoftDeletes;

    public $table = 'provincias';
    

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
        'nombre' => 'required|unique:provincias,nombre,{:id},id'
    ];

    
}
