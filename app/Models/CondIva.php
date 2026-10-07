<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CondIva
 * @package App\Models
 * @version December 26, 2017, 6:22 pm UTC
 *
 * @property string nombre
 */
class CondIva extends Model
{
    use SoftDeletes;

    public $table = 'cond_iva';
    

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
        'nombre' => 'required|unique:cond_iva,nombre,{:id},id'
    ];

    
}
