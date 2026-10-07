<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TipoSociedad
 * @package App\Models
 * @version December 26, 2017, 5:41 pm UTC
 *
 * @property string nombre
 */
class TipoSociedad extends Model
{
    use SoftDeletes;

    public $table = 'tipo_sociedades';
    

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
        'nombre' => 'required|unique:tipo_sociedades,nombre,{:id},id'
    ];

    
}
