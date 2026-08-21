<?php

namespace App\Repositories;

use App\Models\TipoSociedad;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class TipoSociedadRepository
 * @package App\Repositories
 * @version December 26, 2017, 5:41 pm UTC
 *
 * @method TipoSociedad findWithoutFail($id, $columns = ['*'])
 * @method TipoSociedad find($id, $columns = ['*'])
 * @method TipoSociedad first($columns = ['*'])
*/
class TipoSociedadRepository extends BaseRepository
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
        return TipoSociedad::class;
    }
}
