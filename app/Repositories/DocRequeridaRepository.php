<?php

namespace App\Repositories;

use App\Models\DocRequerida;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class DocRequeridaRepository
 * @package App\Repositories
 * @version December 29, 2017, 7:54 pm UTC
 *
 * @method DocRequerida findWithoutFail($id, $columns = ['*'])
 * @method DocRequerida find($id, $columns = ['*'])
 * @method DocRequerida first($columns = ['*'])
*/
class DocRequeridaRepository extends BaseRepository
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
        return DocRequerida::class;
    }
}
