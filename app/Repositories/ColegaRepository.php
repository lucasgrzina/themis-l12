<?php

namespace App\Repositories;

use App\Models\Colega;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class ColegaRepository
 * @package App\Repositories
 * @version December 26, 2017, 7:02 pm UTC
 *
 * @method Colega findWithoutFail($id, $columns = ['*'])
 * @method Colega find($id, $columns = ['*'])
 * @method Colega first($columns = ['*'])
*/
class ColegaRepository extends BaseRepository
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
        return Colega::class;
    }
}
