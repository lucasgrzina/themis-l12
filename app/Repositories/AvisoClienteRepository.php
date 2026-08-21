<?php

namespace App\Repositories;

use App\Models\AvisoCliente;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class AvisoClienteRepository
 * @package App\Repositories
 * @version May 28, 2018, 4:09 pm -03
 *
 * @method AvisoCliente findWithoutFail($id, $columns = ['*'])
 * @method AvisoCliente find($id, $columns = ['*'])
 * @method AvisoCliente first($columns = ['*'])
*/
class AvisoClienteRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'usuario_id',
        'cliente_id',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return AvisoCliente::class;
    }
}
