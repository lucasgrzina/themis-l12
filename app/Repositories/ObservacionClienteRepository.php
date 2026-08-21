<?php

namespace App\Repositories;

use App\Models\ObservacionCliente;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class ObservacionClienteRepository
 * @package App\Repositories
 * @version March 15, 2018, 4:36 pm UTC
 *
 * @method ObservacionCliente findWithoutFail($id, $columns = ['*'])
 * @method ObservacionCliente find($id, $columns = ['*'])
 * @method ObservacionCliente first($columns = ['*'])
*/
class ObservacionClienteRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return ObservacionCliente::class;
    }
}
