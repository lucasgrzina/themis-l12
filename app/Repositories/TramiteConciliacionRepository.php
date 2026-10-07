<?php

namespace App\Repositories;

use App\Models\TramiteConciliacion;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class TramiteConciliacionRepository
 * @package App\Repositories
 * @version April 11, 2018, 6:23 pm UTC
 *
 * @method TramiteConciliacion findWithoutFail($id, $columns = ['*'])
 * @method TramiteConciliacion find($id, $columns = ['*'])
 * @method TramiteConciliacion first($columns = ['*'])
*/
class TramiteConciliacionRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'tramite_id',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return TramiteConciliacion::class;
    }
}
