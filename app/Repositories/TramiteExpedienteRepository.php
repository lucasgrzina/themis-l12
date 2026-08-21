<?php

namespace App\Repositories;

use App\Models\TramiteExpediente;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class TramiteExpedienteRepository
 * @package App\Repositories
 * @version April 23, 2018, 7:21 pm UTC
 *
 * @method TramiteExpediente findWithoutFail($id, $columns = ['*'])
 * @method TramiteExpediente find($id, $columns = ['*'])
 * @method TramiteExpediente first($columns = ['*'])
*/
class TramiteExpedienteRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'tramite_id',
        'area_id',
        'nro_expediente',
        'juzgado_id',
        'fecha',
        'estado_id',
        'vuelta_anses'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return TramiteExpediente::class;
    }
}
