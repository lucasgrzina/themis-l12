<?php

namespace App\Repositories;

use App\Models\TipoTramite;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class TipoTramiteRepository
 * @package App\Repositories
 * @version December 26, 2017, 3:27 pm UTC
 *
 * @method TipoTramite findWithoutFail($id, $columns = ['*'])
 * @method TipoTramite find($id, $columns = ['*'])
 * @method TipoTramite first($columns = ['*'])
*/
class TipoTramiteRepository extends BaseRepository
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
        return TipoTramite::class;
    }
}
