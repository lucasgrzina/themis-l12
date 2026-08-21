<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Juzgado
 * @package App\Models
 * @version December 28, 2017, 2:05 pm UTC
 *
 * @property string nombre
 * @property boolean vigente
 */
class Juzgado extends Model
{
    use SoftDeletes;

    public $table = 'juzgados';
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'nombre',
        'vigente',
        'area_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'nombre' => 'string',
        'vigente' => 'boolean',
        'area_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'nombre' => 'required|unique:paises,nombre,{:id},id'
    ];

    public function area() 
    {
        return $this->belongsTo('App\Models\Area','area_id');
    }      
}
