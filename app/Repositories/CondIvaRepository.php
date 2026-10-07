<?php

namespace App\Repositories;

use App\Models\CondIva;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class CondIvaRepository
 * @package App\Repositories
 * @version December 26, 2017, 6:22 pm UTC
 *
 * @method CondIva findWithoutFail($id, $columns = ['*'])
 * @method CondIva find($id, $columns = ['*'])
 * @method CondIva first($columns = ['*'])
*/
class CondIvaRepository extends BaseRepository
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
        return CondIva::class;
    }
}
