<?php

namespace App\Repositories;

use App\Models\DocumentoCliente;

/**
 * Class DocumentoClienteRepository
 * @package App\Repositories
 * @version March 8, 2018, 2:31 pm UTC
 *
 * @method DocumentoCliente findWithoutFail($id, $columns = ['*'])
 * @method DocumentoCliente find($id, $columns = ['*'])
 * @method DocumentoCliente first($columns = ['*'])
*/
class DocumentoClienteRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'nombre',
        'nombre_archivo'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return DocumentoCliente::class;
    }
}
