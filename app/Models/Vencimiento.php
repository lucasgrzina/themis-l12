<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Vencimiento
 * @package App\Models
 * @version May 30, 2018, 12:35 pm -03
 *
 * @property string|\Carbon\Carbon fecha
 * @property integer cliente_id
 * @property integer requerimiento_id
 * @property integer tramite_id
 */
class Vencimiento extends Model
{
    use SoftDeletes;

    public $table = 'vencimientos';
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'fecha',
        'cliente_id',
        'requerimiento_id',
        'tramite_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'cliente_id' => 'integer',
        'requerimiento_id' => 'integer',
        'tramite_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'fecha' => 'required',
        'cliente_id' => 'required',
        'requerimiento_id' => 'required'
    ];

    public function vencible()
    {
        return $this->morphTo();
    }
    
}
