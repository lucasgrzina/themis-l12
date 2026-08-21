<?php

namespace App\Repositories;

use App\Models\TramiteCliente;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class TramiteClienteRepository
 * @package App\Repositories
 * @version April 9, 2018, 4:01 pm UTC
 *
 * @method TramiteCliente findWithoutFail($id, $columns = ['*'])
 * @method TramiteCliente find($id, $columns = ['*'])
 * @method TramiteCliente first($columns = ['*'])
*/
class TramiteClienteRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'requerimiento_id',
        'expediente',
        'fecha_inicio',
        'rep_origen_id',
        'estado_tramite_id',
        'secuencia',
        'oficina_ingreso_id',
        'oficina_egreso_id',
        'resolucion',
        'tipo_resolucion',
        'nro_beneficio',
        'fecha_beneficio',
        'observaciones',
        'archivar',
        'fecha_archivo',
        'usuario_archivo_id',
        'fecha_ingreso',
        'fecha_egreso'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return TramiteCliente::class;
    }
}
