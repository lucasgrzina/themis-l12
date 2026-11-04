<?php

namespace App\Repositories;

use App\Models\TimeTipoCambio;

/**
 * Class TimeTipoCambioRepository
 * @package App\Repositories
 *
 * @method TimeTipoCambio findWithoutFail($id, $columns = ['*'])
 * @method TimeTipoCambio find($id, $columns = ['*'])
 * @method TimeTipoCambio first($columns = ['*'])
 */
class TimeTipoCambioRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'nombre',
    ];

    public function model()
    {
        return TimeTipoCambio::class;
    }
}
