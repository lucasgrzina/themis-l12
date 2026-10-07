<?php

namespace App\Repositories;

use App\Models\EstadoRequerimiento;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class EstadoRequerimientoRepository
 * @package App\Repositories
 * @version December 26, 2017, 4:13 pm UTC
 *
 * @method EstadoRequerimiento findWithoutFail($id, $columns = ['*'])
 * @method EstadoRequerimiento find($id, $columns = ['*'])
 * @method EstadoRequerimiento first($columns = ['*'])
*/
class EstadoRequerimientoRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'nombre' => 'like'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return EstadoRequerimiento::class;
    }
}
