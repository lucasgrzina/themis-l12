<?php

namespace App\Repositories;

use App\Models\EstadoExpediente;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class EstadoExpedienteRepository
 * @package App\Repositories
 * @version December 28, 2017, 3:56 pm UTC
 *
 * @method EstadoExpediente findWithoutFail($id, $columns = ['*'])
 * @method EstadoExpediente find($id, $columns = ['*'])
 * @method EstadoExpediente first($columns = ['*'])
*/
class EstadoExpedienteRepository extends BaseRepository
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
        return EstadoExpediente::class;
    }
}
