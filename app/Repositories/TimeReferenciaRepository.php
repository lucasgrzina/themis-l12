<?php

namespace App\Repositories;

use App\Models\TimeReferencia;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class TimeReferenciaRepository
 * @package App\Repositories
 * @version November 29, 2018, 9:31 am -03
 *
 * @method TimeReferencia findWithoutFail($id, $columns = ['*'])
 * @method TimeReferencia find($id, $columns = ['*'])
 * @method TimeReferencia first($columns = ['*'])
*/
class TimeReferenciaRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'nombre' => 'like',
        'cliente_id'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return TimeReferencia::class;
    }
}
