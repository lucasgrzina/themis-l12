<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Catalogo de categorias de abogado (determina la tarifa del Time).
 * Se referencia por FK desde users.categoria_abogado_id y time_tarifas.categoria_id.
 */
class TimeCategoriaAbogado extends Model
{
    use SoftDeletes;

    public $table = 'time_categorias_abogado';

    protected $dates = ['deleted_at'];

    public $fillable = ['nombre'];

    protected $casts = ['nombre' => 'string'];

    public static $rules = [
        'nombre' => 'required|max:50|unique:time_categorias_abogado,nombre,{:id},id,deleted_at,NULL',
    ];

    /** Cantidad de abogados y tarifas que usan esta categoria. */
    public static function usos($id)
    {
        return \App\User::where('categoria_abogado_id', $id)->count()
            + TimeTarifa::where('categoria_id', $id)->count();
    }
}
