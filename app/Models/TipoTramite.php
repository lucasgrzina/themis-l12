<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TipoTramite
 * @package App\Models
 * @version December 26, 2017, 3:27 pm UTC
 *
 * @property string nombre
 * @property boolean vigente
 */
class TipoTramite extends Model
{
    use SoftDeletes;

    public $table = 'tipo_tramites';
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'nombre',
        'vigente',
        'area_id',
        'tratamiento',
        'inicia_tramite'
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
        'tratamiento' => 'string',
        'inicia_tramite' => 'boolean',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'nombre' => 'required|unique:tipo_tramites,nombre,{:id},id',
        'area_id' => 'required',
        'inicia_tramite' => 'boolean'
    ];

    public function doc_requerida() {
        return $this->hasMany('App\Models\TipoTraDocReq','tipo_tramite_id');
    }    

    public function area() 
    {
        return $this->belongsTo('App\Models\Area','area_id');
    }      

    public function requerimientos() 
    {
        return $this->hasMany('App\Models\RequerimientoCliente','tipo_tramite_id');
    } 

}
