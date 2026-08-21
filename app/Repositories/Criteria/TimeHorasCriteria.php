<?php

namespace App\Repositories\Criteria;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;

class TimeHorasCriteria implements CriteriaInterface
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
        if (request()->get('desde','') !== '')
        {
            $model->where('fecha','>=',Carbon::createFromFormat('d/m/Y',request()->get('desde'))->format('Y-m-d'));
        }
        if (request()->get('hasta','') !== '')
        {
            $model->where('fecha','<=',Carbon::createFromFormat('d/m/Y',request()->get('hasta'))->format('Y-m-d'));
        }

        if (request()->has('user_id'))
        {
            if (request()->get('user_id',null) !== null)
            {
                $model->where('user_id',request()->get('user_id'));
            }
        }
        else
        {
            $model->where('user_id',request()->user()->id);
        }

        if (request()->get('cliente_id',null) !== null)
        {
            $model->where('cliente_id',request()->get('cliente_id'));
        }                
        return $model;
    }
}
