<?php

namespace App\Repositories;

use App\Models\TimeParticipacion;

/**
 * Class TimeParticipacionRepository
 * @package App\Repositories
 *
 * @method TimeParticipacion findWithoutFail($id, $columns = ['*'])
 * @method TimeParticipacion find($id, $columns = ['*'])
 * @method TimeParticipacion first($columns = ['*'])
 */
class TimeParticipacionRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'cliente_id',
    ];

    public function model()
    {
        return TimeParticipacion::class;
    }
}
