<?php

namespace App\Repositories;

use App\Models\TimeClienteParametro;

class TimeClienteParametroRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'cliente_id',
    ];

    public function model()
    {
        return TimeClienteParametro::class;
    }
}
