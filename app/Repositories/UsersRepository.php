<?php

namespace App\Repositories;

use App\User;

/**
 * Class UsersRepositoryRepositoryEloquent
 * @package namespace App\Repositories;
 */
class UsersRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name' => 'like',
        'username' => 'like',
        'email' => 'like'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return User::class;
    }
}
