<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Opciones administrables del wizard de alta rapida (ver migracion catalogo_opciones).
 */
class CatalogoOpcion extends Model
{
    use SoftDeletes;

    public $table = 'catalogo_opciones';

    const GRUPOS = [
        'motivo_aviso'   => 'Motivos de aviso',
        'tipo_contacto'  => 'Tipos de contacto',
        'tipo_documento' => 'Tipos de documento adjunto',
    ];

    protected $dates = ['deleted_at'];

    public $fillable = ['grupo', 'nombre', 'orden', 'vigente'];

    protected $casts = [
        'orden'   => 'integer',
        'vigente' => 'boolean',
    ];

    public static function rules()
    {
        return [
            'grupo'  => 'required|in:' . implode(',', array_keys(self::GRUPOS)),
            'nombre' => 'required|max:150',
        ];
    }

    public function scopeVigentes($query)
    {
        return $query->where('vigente', true);
    }

    /** Opciones vigentes de un grupo, listas para un combo. */
    public static function combo($grupo)
    {
        return self::vigentes()->where('grupo', $grupo)
            ->orderBy('orden')->orderBy('nombre')->get(['id', 'nombre'])->toArray();
    }
}
