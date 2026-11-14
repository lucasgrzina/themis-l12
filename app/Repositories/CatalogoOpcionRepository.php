<?php

namespace App\Repositories;

use App\Models\CatalogoOpcion;

/**
 * @method CatalogoOpcion findWithoutFail($id, $columns = ['*'])
 * @method CatalogoOpcion find($id, $columns = ['*'])
 */
class CatalogoOpcionRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'nombre' => 'like',
        'grupo',
    ];

    public function model()
    {
        return CatalogoOpcion::class;
    }
}
