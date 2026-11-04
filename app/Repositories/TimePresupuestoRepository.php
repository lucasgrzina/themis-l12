<?php

namespace App\Repositories;

use App\Models\TimePresupuesto;

/**
 * Class TimePresupuestoRepository
 * @package App\Repositories
 *
 * @method TimePresupuesto findWithoutFail($id, $columns = ['*'])
 * @method TimePresupuesto find($id, $columns = ['*'])
 * @method TimePresupuesto first($columns = ['*'])
 */
class TimePresupuestoRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'cliente_id',
    ];

    public function model()
    {
        return TimePresupuesto::class;
    }
}
