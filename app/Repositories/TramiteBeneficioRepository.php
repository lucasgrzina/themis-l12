<?php

namespace App\Repositories;

use App\Models\TramiteBeneficio;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class TramiteBeneficioRepository
 * @package App\Repositories
 * @version April 11, 2018, 6:23 pm UTC
 *
 * @method TramiteBeneficio findWithoutFail($id, $columns = ['*'])
 * @method TramiteBeneficio find($id, $columns = ['*'])
 * @method TramiteBeneficio first($columns = ['*'])
*/
class TramiteBeneficioRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'tramite_id',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return TramiteBeneficio::class;
    }
}
