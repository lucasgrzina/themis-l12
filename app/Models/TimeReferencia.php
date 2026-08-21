<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TimeReferencia
 * @package App\Models
 * @version November 29, 2018, 9:31 am -03
 *
 * @property integer cliente_id
 * @property string nombre
 */
class TimeReferencia extends Model
{
    use SoftDeletes;

    public $table = 'time_referencias';
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'cliente_id',
        'nombre'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'cliente_id' => 'integer',
        'nombre' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'cliente_id' => 'required',
        'nombre' => 'required|unique:time_referencias,nombre,{:id},id,cliente_id,{:cliente_id}',
    ];

    
}
