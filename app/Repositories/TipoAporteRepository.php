<?php

namespace App\Repositories;

use App\Models\TipoAporte;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class TipoAporteRepository
 * @package App\Repositories
 * @version December 26, 2017, 2:07 pm UTC
 *
 * @method TipoAporte findWithoutFail($id, $columns = ['*'])
 * @method TipoAporte find($id, $columns = ['*'])
 * @method TipoAporte first($columns = ['*'])
*/
class TipoAporteRepository extends BaseRepository
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
        return TipoAporte::class;
    }
}
