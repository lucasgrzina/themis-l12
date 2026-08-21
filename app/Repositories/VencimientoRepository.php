<?php

namespace App\Repositories;

use App\Models\Vencimiento;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class VencimientoRepository
 * @package App\Repositories
 * @version May 30, 2018, 12:35 pm -03
 *
 * @method Vencimiento findWithoutFail($id, $columns = ['*'])
 * @method Vencimiento find($id, $columns = ['*'])
 * @method Vencimiento first($columns = ['*'])
*/
class VencimientoRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'fecha',
        'cliente_id',
        'requerimiento_id',
        'tramite_id'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Vencimiento::class;
    }
}
