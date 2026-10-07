<?php

namespace App\Repositories;


//use InfyOm\Generator\Common\BaseRepository;
use Spatie\Permission\Models\Role;

/**
 * Class AccionesControladasRepository
 * @package App\Repositories
 * @version November 17, 2017, 7:16 pm UTC
 *
 * @method AccionesControladas findWithoutFail($id, $columns = ['*'])
 * @method AccionesControladas find($id, $columns = ['*'])
 * @method AccionesControladas first($columns = ['*'])
*/
class RolesRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name' => 'like'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Role::class;
    }
}
