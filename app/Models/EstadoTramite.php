<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class EstadoTramite
 * @package App\Models
 * @version December 26, 2017, 4:47 pm UTC
 *
 * @property string nombre
 * @property boolean vigente
 */
class EstadoTramite extends Model
{
    use SoftDeletes;

    public $table = 'estado_tramites';
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'nombre',
        'vigente',
        'area_id',
        'modificable'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'nombre' => 'string',
        'vigente' => 'boolean',
        'area_id' => 'integer',
        'modificable' => 'boolean'

    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'nombre' => 'required|unique:estado_tramites,nombre,{:id},id,area_id,{:area_id}',
        //'area_id' => 'required'
    ];

    public function area() 
    {
        return $this->belongsTo('App\Models\Area','area_id');
    }  
    
}
