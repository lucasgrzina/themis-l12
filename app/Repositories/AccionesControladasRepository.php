<?php

namespace App\Repositories;

use App\Models\AccionesControladas;

/**
 * Class AccionesControladasRepository
 * @package App\Repositories
 * @version November 17, 2017, 7:16 pm UTC
 *
 * @method AccionesControladas findWithoutFail($id, $columns = ['*'])
 * @method AccionesControladas find($id, $columns = ['*'])
 * @method AccionesControladas first($columns = ['*'])
*/
class AccionesControladasRepository extends BaseRepository
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
        return AccionesControladas::class;
    }
}
