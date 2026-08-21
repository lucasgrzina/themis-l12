<?php

namespace App\Repositories;

use App\Models\ReparticionOrigen;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class ReparticionOrigenRepository
 * @package App\Repositories
 * @version December 28, 2017, 1:16 pm UTC
 *
 * @method ReparticionOrigen findWithoutFail($id, $columns = ['*'])
 * @method ReparticionOrigen find($id, $columns = ['*'])
 * @method ReparticionOrigen first($columns = ['*'])
*/
class ReparticionOrigenRepository extends BaseRepository
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
        return ReparticionOrigen::class;
    }
}
