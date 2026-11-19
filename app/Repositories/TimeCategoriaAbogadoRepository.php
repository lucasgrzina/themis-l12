<?php

namespace App\Repositories;

use App\Models\TimeCategoriaAbogado;

class TimeCategoriaAbogadoRepository extends BaseRepository
{
    protected $fieldSearchable = ['nombre' => 'like'];

    public function model()
    {
        return TimeCategoriaAbogado::class;
    }
}
