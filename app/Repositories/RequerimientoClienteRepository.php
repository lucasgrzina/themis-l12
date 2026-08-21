<?php

namespace App\Repositories;

use App\Models\RequerimientoCliente;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class RequerimientoClienteRepository
 * @package App\Repositories
 * @version March 16, 2018, 2:17 pm UTC
 *
 * @method RequerimientoCliente findWithoutFail($id, $columns = ['*'])
 * @method RequerimientoCliente find($id, $columns = ['*'])
 * @method RequerimientoCliente first($columns = ['*'])
*/
class RequerimientoClienteRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'area_id',
        'cliente_id',
        'colega_id',
        'tipo_tramite_id',
        'recomendado',
        'nombre_causante',
        'tipo_doc_id_causante',
        'nro_doc_causante',
        'domicilio_causante',
        'estado_civil',
        'fecha_mat_causante',
        'fecha_conv_causante',
        'fecha_fallecimiento_causante',
        'hijos',
        'req_nec_id',
        'estado_req_id',
        'tramite_id',
        'fecha_turno',
        'hora_turno',
        'rep_origen_id',
        'documentacion'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return RequerimientoCliente::class;
    }
}
