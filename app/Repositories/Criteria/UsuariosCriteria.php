<?php

namespace App\Repositories\Criteria;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;

class UsuariosCriteria implements CriteriaInterface
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Apply criteria in query repository.
     *
     * @param $model
     * @param \Prettus\Repository\Contracts\RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, \Prettus\Repository\Contracts\RepositoryInterface $repository)
    {
        if (request()->get('time',null) !== null)
        {
            switch (request()->get('time')) 
            {
                case 'todos':
                    $model->whereNotNull('time_level');
                    break;
                default:
                    $model->whereTimeLevel(request()->get('time'));
                    break;                
            }
        }
        return $model;
    }
}
