<?php

namespace App\Repositories;

use App\Models\TimeGestion;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class TimeGestionRepository
 * @package App\Repositories
 * @version November 27, 2018, 4:54 pm -03
 *
 * @method TimeGestion findWithoutFail($id, $columns = ['*'])
 * @method TimeGestion find($id, $columns = ['*'])
 * @method TimeGestion first($columns = ['*'])
*/
class TimeGestionRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'nombre' => 'like',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return TimeGestion::class;
    }
}
