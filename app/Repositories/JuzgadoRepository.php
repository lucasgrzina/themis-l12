<?php

namespace App\Repositories;

use App\Models\Juzgado;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class JuzgadoRepository
 * @package App\Repositories
 * @version December 28, 2017, 2:05 pm UTC
 *
 * @method Juzgado findWithoutFail($id, $columns = ['*'])
 * @method Juzgado find($id, $columns = ['*'])
 * @method Juzgado first($columns = ['*'])
*/
class JuzgadoRepository extends BaseRepository
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
        return Juzgado::class;
    }
}
