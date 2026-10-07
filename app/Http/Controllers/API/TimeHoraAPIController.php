<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateUpdateTimeHoraAPIRequest;
use App\Models\TimeHora;
use App\Repositories\Criteria\CustomDataTableCriteria;
use App\Repositories\Criteria\TimeHorasCriteria;
use App\Repositories\TimeHoraRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class TimeHoraController
 * @package App\Http\Controllers\API
 */

class TimeHoraAPIController extends AppBaseController
{
    /** @var  TimeHoraRepository */
    private $repository;

    public function __construct(TimeHoraRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the TimeHora.
     * GET|HEAD /timeHoras
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->repository->pushCriteria(new TimeHorasCriteria($request));
        //$this->repository->pushCriteria(new CustomDataTableCriteria($request));
        //$this->repository->pushCriteria(new RequestCriteria($request));

        $collection = $this->repository->with([
            'cliente' => function($q) {
                $q->select('nombre_completo','id');
            },
            'usuario' => function($q) {
                $q->select('name','username','id');
            },  
            'gestion' => function($q) {
                $q->select('nombre','id');
            },                                  
        ])->orderBy('fecha','asc')->paginate($request->get('per_page',50));

        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }

    /**
     * Store a newly created TimeHora in storage.
     * POST /timeHoras
     *
     * @param CreateTimeHoraAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateTimeHoraAPIRequest $request)
    {
        $input = $request->except(['gestion','cliente','usuario']);

        try
        {
            DB::beginTransaction();
            $input['fecha'] = Carbon::createFromFormat('Y-m-d',\Illuminate\Support\Str::limit($input['fecha_dp'],10,''))->format('Y-m-d');
            $input['user_id'] = request()->user()->id;
            $model = $this->repository->create($input);
            DB::commit();
            return $this->sendResponse($model->toArray(), trans('api.success'));
        }
        catch(\Exception $e)
        {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Display the specified TimeHora.
     * GET|HEAD /timeHoras/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Update the specified TimeHora in storage.
     * PUT/PATCH /timeHoras/{id}
     *
     * @param  int $id
     * @param UpdateTimeHoraAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateTimeHoraAPIRequest $request)
    {
        $input = $request->except(['gestion','cliente','usuario']);

        try
        {
            DB::beginTransaction();        
            $input['fecha'] = Carbon::createFromFormat('Y-m-d',\Illuminate\Support\Str::limit($input['fecha_dp'],10,''))->format('Y-m-d');
            /** @var TimeHora $model */
            $model = $this->repository->findWithoutFail($id);

            if (empty($model)) {
                throw new \Exception(trans('api.not_found'), 1);
            }

            $model = $this->repository->update($input, $id);

            DB::commit();
            return $this->sendResponse($model->toArray(), trans('api.success'));
        }
        catch(\Exception $e)
        {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }

    }

    /**
     * Remove the specified TimeHora from storage.
     * DELETE /timeHoras/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var TimeHora $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model->delete();

        return $this->sendResponse($id, trans('api.success'));
    }

    public function removeSelected(Request $request)
    {
        $ids = $request->get('ids',[]);
        $this->repository->deleteByIds($ids);
        return $this->sendResponse($ids, trans('api.success'));   
    }    

    public function facturar(Request $request)
    {
        $input = $request->only(['id','facturar']);

        try
        {
            DB::beginTransaction();        

            $model = $this->repository->findWithoutFail($input['id']);

            if (empty($model)) {
                throw new \Exception(trans('api.not_found'), 1);
            }

            $model->facturar = $input['facturar'];
            $model->save();

            DB::commit();
            return $this->sendResponse($model->toArray(), trans('api.success'));
        }
        catch(\Exception $e)
        {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }    
}
