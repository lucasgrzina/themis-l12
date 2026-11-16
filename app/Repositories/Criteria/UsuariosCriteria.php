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

        if (request()->filled('role')) {
            $roleId = request()->get('role');
            $model = $model->whereHas('roles', function ($q) use ($roleId) {
                $q->where('roles.id', $roleId);
            });
        }

        // Orden por rol (relacion): se resuelve aca y se quita del orderBy generico
        $orders = array_filter(explode(';', (string) request()->get('orderBy', '')));
        $dirs = explode(';', (string) request()->get('sortedBy', 'asc'));
        $keep = [];
        $keepDirs = [];
        foreach (array_values($orders) as $i => $field) {
            $dir = $dirs[$i] ?? $dirs[0];
            if ($field === 'roles') {
                $pivot = config('permission.table_names.model_has_roles', 'model_has_roles');
                $model = $model->orderBy(
                    \DB::table('roles')
                        ->join($pivot, $pivot . '.role_id', '=', 'roles.id')
                        ->whereColumn($pivot . '.model_id', 'users.id')
                        ->select('roles.name')
                        ->limit(1),
                    $dir === 'asc' ? 'asc' : 'desc'
                );
            } else {
                $keep[] = $field;
                $keepDirs[] = $dir;
            }
        }
        request()->merge([
            'orderBy' => implode(';', $keep),
            'sortedBy' => $keepDirs ? implode(';', $keepDirs) : 'asc',
        ]);

        return $model;
    }
}
