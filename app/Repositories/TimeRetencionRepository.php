<?php

namespace App\Repositories;

use App\Models\TimeRetencion;

/**
 * Class TimeRetencionRepository
 * @package App\Repositories
 *
 * @method TimeRetencion findWithoutFail($id, $columns = ['*'])
 * @method TimeRetencion find($id, $columns = ['*'])
 * @method TimeRetencion first($columns = ['*'])
 */
class TimeRetencionRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'codigo',
    ];

    public function model()
    {
        return TimeRetencion::class;
    }
}
