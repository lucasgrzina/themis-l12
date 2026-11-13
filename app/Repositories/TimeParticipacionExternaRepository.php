<?php

namespace App\Repositories;

use App\Models\TimeParticipacionExterna;

/**
 * Class TimeParticipacionExternaRepository
 * @package App\Repositories
 *
 * @method TimeParticipacionExterna findWithoutFail($id, $columns = ['*'])
 * @method TimeParticipacionExterna find($id, $columns = ['*'])
 * @method TimeParticipacionExterna first($columns = ['*'])
 */
class TimeParticipacionExternaRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'cliente_id',
        'nombre',
    ];

    public function model()
    {
        return TimeParticipacionExterna::class;
    }
}
