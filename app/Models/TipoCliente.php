<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TipoCliente
 * @package App\Models
 * @version April 4, 2018, 6:22 pm UTC
 *
 * @property string nombre
 * @property string sigla
 */
class TipoCliente extends Model
{
    use SoftDeletes;

    public $table = 'tipo_clientes';
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'nombre',
        'sigla'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'nombre' => 'string',
        'sigla' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'nombre' => 'required',
        'sigla' => 'required'
    ];

    public function doc_requerida() {
        return $this->hasMany('App\Models\TipoClienteDocReq','tipo_cliente_id');
    }     
}
