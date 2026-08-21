<?php

namespace App\Repositories;

use App\Models\EmpresaReferencia;
//use InfyOm\Generator\Common\BaseRepository;

/**
 * Class EmpresaReferenciaRepository
 * @package App\Repositories
 * @version December 27, 2017, 6:32 pm UTC
 *
 * @method EmpresaReferencia findWithoutFail($id, $columns = ['*'])
 * @method EmpresaReferencia find($id, $columns = ['*'])
 * @method EmpresaReferencia first($columns = ['*'])
*/
class EmpresaReferenciaRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'razon_social' => 'like',
        'persona_referencia' => 'like',
        'cuit' => 'like',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return EmpresaReferencia::class;
    }
}
