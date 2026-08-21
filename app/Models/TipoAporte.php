<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TipoAporte
 * @package App\Models
 * @version December 26, 2017, 2:07 pm UTC
 *
 * @property string nombre
 */
class TipoAporte extends Model
{
    use SoftDeletes;

    public $table = 'tipo_aportes';
    

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
        'nombre' => 'required|unique:tipo_aportes,nombre,{:id},id'
    ];

    public function doc_requerida() {
        return $this->hasMany('App\Models\TipoAporteDocReq','tipo_aporte_id');
    }      
}
