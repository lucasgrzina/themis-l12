<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class EstadoExpediente
 * @package App\Models
 * @version December 28, 2017, 3:56 pm UTC
 *
 * @property string nombre
 * @property boolean vigente
 */
class EstadoExpediente extends Model
{
    use SoftDeletes;

    public $table = 'estados_expediente';
    

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
        'nombre' => 'required|unique:estado_tramites,nombre,{:id},id'
    ];

    
}
