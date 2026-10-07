<?php

namespace App\Repositories;

use App\Models\Pais;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class PaisRepository
 * @package App\Repositories
 * @version December 21, 2017, 7:39 pm UTC
 *
 * @method Pais findWithoutFail($id, $columns = ['*'])
 * @method Pais find($id, $columns = ['*'])
 * @method Pais first($columns = ['*'])
*/
class PaisRepository extends BaseRepository
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
        return Pais::class;
    }
}
