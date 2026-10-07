<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DocRequerida
 * @package App\Models
 * @version December 29, 2017, 7:54 pm UTC
 *
 * @property string nombre
 * @property smalltext descripcion
 * @property string areas
 */
class DocRequerida extends Model
{
    use SoftDeletes;

    public $table = 'doc_requerida';
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'nombre',
        'descripcion',
        'tags'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'nombre' => 'string',
        'tags' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'nombre' => 'required|unique:doc_requerida,nombre,{:id},id'
    ];

    
}
