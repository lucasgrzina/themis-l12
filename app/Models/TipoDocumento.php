<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TipoDocumento
 * @package App\Models
 * @version December 12, 2017, 7:35 pm UTC
 *
 * @property string nombre
 */
class TipoDocumento extends Model
{
    use SoftDeletes;

    public $table = 'tipo_documentos';
    

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
        'nombre' => 'required|unique:tipo_documentos,nombre,{:id},id'
    ];

    
}
