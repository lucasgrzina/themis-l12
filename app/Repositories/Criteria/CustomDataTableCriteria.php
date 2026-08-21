<?php

namespace App\Repositories\Criteria;

use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;

class CustomDataTableCriteria implements CriteriaInterface
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
        $sortOrder = explode('|',$this->request->get('sort', '|'));
        $order = $sortOrder[0];
        $sorted = $sortOrder[1];


        $this->request['orderBy'] = $order;
        $this->request['sortedBy'] = ($sorted ? $sorted : 'desc');

        return $model;
    }
}
