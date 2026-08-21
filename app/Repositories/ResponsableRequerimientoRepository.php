<?php

namespace App\Repositories;

use App\Models\ResponsableRequerimiento;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class ResponsableRequerimientoRepository
 * @package App\Repositories
 * @version March 19, 2018, 3:20 pm UTC
 *
 * @method ResponsableRequerimiento findWithoutFail($id, $columns = ['*'])
 * @method ResponsableRequerimiento find($id, $columns = ['*'])
 * @method ResponsableRequerimiento first($columns = ['*'])
*/
class ResponsableRequerimientoRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'requerimiento_id',
        'user_id',
        'fecha_asignacion'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return ResponsableRequerimiento::class;
    }
}
