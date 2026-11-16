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
        // Vuetable con multiSort envia "campo|dir,campo2|dir2"
        $orders = [];
        $dirs = [];
        foreach (explode(',', (string) $this->request->get('sort', '')) as $part) {
            $pieces = explode('|', $part);
            $field = trim($pieces[0]);
            if ($field === '') {
                continue;
            }
            $dir = strtolower(trim($pieces[1] ?? ''));
            $orders[] = $field;
            $dirs[] = in_array($dir, ['asc', 'desc']) ? $dir : 'desc';
        }

        $this->request['orderBy'] = implode(';', $orders);
        $this->request['sortedBy'] = $dirs ? implode(';', $dirs) : 'desc';

        return $model;
    }
}
