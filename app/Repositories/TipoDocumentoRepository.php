<?php

namespace App\Repositories;

use App\Models\TipoDocumento;


/**
 * Class TipoDocumentoRepository
 * @package App\Repositories
 * @version December 12, 2017, 7:35 pm UTC
 *
 * @method TipoDocumento findWithoutFail($id, $columns = ['*'])
 * @method TipoDocumento find($id, $columns = ['*'])
 * @method TipoDocumento first($columns = ['*'])
*/
class TipoDocumentoRepository extends BaseRepository
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
        return TipoDocumento::class;
    }
}
