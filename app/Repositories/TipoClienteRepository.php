<?php

namespace App\Repositories;

use App\Models\TipoCliente;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class TipoClienteRepository
 * @package App\Repositories
 * @version April 4, 2018, 6:22 pm UTC
 *
 * @method TipoCliente findWithoutFail($id, $columns = ['*'])
 * @method TipoCliente find($id, $columns = ['*'])
 * @method TipoCliente first($columns = ['*'])
*/
class TipoClienteRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'nombre',
        'sigla'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return TipoCliente::class;
    }
}
