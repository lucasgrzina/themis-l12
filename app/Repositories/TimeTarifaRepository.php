<?php

namespace App\Repositories;

use App\Models\TimeTarifa;

/**
 * Class TimeTarifaRepository
 * @package App\Repositories
 *
 * @method TimeTarifa findWithoutFail($id, $columns = ['*'])
 * @method TimeTarifa find($id, $columns = ['*'])
 * @method TimeTarifa first($columns = ['*'])
 */
class TimeTarifaRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'cliente_id',
    ];

    public function model()
    {
        return TimeTarifa::class;
    }
}
