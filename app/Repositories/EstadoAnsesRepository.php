<?php

namespace App\Repositories;

use App\Models\EstadoAnses;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class EstadoAnsesRepository
 * @package App\Repositories
 * @version May 18, 2018, 12:59 pm UTC
 *
 * @method EstadoAnses findWithoutFail($id, $columns = ['*'])
 * @method EstadoAnses find($id, $columns = ['*'])
 * @method EstadoAnses first($columns = ['*'])
*/
class EstadoAnsesRepository extends BaseRepository
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
        return EstadoAnses::class;
    }
}
