<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class EstadoAnses
 * @package App\Models
 * @version May 18, 2018, 12:59 pm UTC
 *
 * @property string nombre
 * @property boolean vigente
 */
class EstadoAnses extends Model
{
    use SoftDeletes;

    public $table = 'estado_anses';
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'nombre',
        'vigente'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'nombre' => 'string',
        'vigente' => 'boolean'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'nombre' => 'required|unique:estado_anses,nombre,{:id},id',
    ];

    
}
