<?php

namespace App\Repositories;

use App\Models\EstadoTramite;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class EstadoTramiteRepository
 * @package App\Repositories
 * @version December 26, 2017, 4:47 pm UTC
 *
 * @method EstadoTramite findWithoutFail($id, $columns = ['*'])
 * @method EstadoTramite find($id, $columns = ['*'])
 * @method EstadoTramite first($columns = ['*'])
*/
class EstadoTramiteRepository extends BaseRepository
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
        return EstadoTramite::class;
    }
}
