<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class EmpresaReferencia
 * @package App\Models
 * @version December 27, 2017, 6:32 pm UTC
 *
 * @property string razon_social
 * @property string direccion
 * @property string telefonos
 * @property string localidad
 * @property string persona_referencia
 * @property string cuit
 * @property integer id_cond_iva
 * @property string observaciones
 */
class EmpresaReferencia extends Model
{
    use SoftDeletes;

    public $table = 'empresas_referencia';
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'razon_social',
        'direccion',
        'telefonos',
        'localidad',
        'persona_referencia',
        'cuit',
        'id_cond_iva',
        'observaciones'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'razon_social' => 'string',
        'direccion' => 'string',
        'telefonos' => 'string',
        'localidad' => 'string',
        'persona_referencia' => 'string',
        'cuit' => 'string',
        'id_cond_iva' => 'integer',
        'observaciones' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'razon_social' => 'required'
    ];

    
}
