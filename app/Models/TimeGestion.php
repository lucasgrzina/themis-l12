<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TimeGestion
 * @package App\Models
 * @version November 27, 2018, 4:54 pm -03
 *
 * @property string nombre
 * @property boolean vigente
 */
class TimeGestion extends Model
{
    use SoftDeletes;

    public $table = 'time_gestiones';
    

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
        'nombre' => 'required|unique:time_gestiones,nombre,{:id},id',
        'vigente' => 'boolean'
    ];

    public function horas() 
    {
        return $this->hasMany('App\Models\TimeHora','gestion_id');
    }     
    
}
