<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateUpdateTimeGestionAPIRequest;
use App\Models\TimeGestion;
use App\Repositories\TimeGestionRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
//use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Repositories\Criteria\CustomDataTableCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class TimeGestionController
 * @package App\Http\Controllers\API
 */

class TimeGestionAPIController extends AppBaseController
{
    /** @var  TimeGestionRepository */
    private $repository;

    public function __construct(TimeGestionRepository $repo)
    {
        $this->repository = $repo;
    }

    /**
     * Display a listing of the TimeGestion.
     * GET|HEAD /timeGestions
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->repository->pushCriteria(new CustomDataTableCriteria($request));
        $this->repository->pushCriteria(new RequestCriteria($request));
        $collection = $this->repository->paginate($request->get('per_page',10));

        return $this->sendResponse($collection->toArray(), trans('api.success'));
    }

    /**
     * Store a newly created TimeGestion in storage.
     * POST /timeGestions
     *
     * @param CreateTimeGestionAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateTimeGestionAPIRequest $request)
    {
        $input = $request->all();

        $model = $this->repository->create($input);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Display the specified TimeGestion.
     * GET|HEAD /timeGestions/{id}
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
     * Update the specified TimeGestion in storage.
     * PUT/PATCH /timeGestions/{id}
     *
     * @param  int $id
     * @param UpdateTimeGestionAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateTimeGestionAPIRequest $request)
    {
        $input = $request->all();

        /** @var TimeGestion $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    /**
     * Remove the specified TimeGestion from storage.
     * DELETE /timeGestions/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var TimeGestion $model */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(trans('api.not_found'));
        }
        
        try
        {
            $model->delete();    
        }
        catch(\Exception $e)
        {
            return $this->sendError($e->getMessage(),500);
        }
        

        return $this->sendResponse($id, trans('api.success'));
    }

    public function removeSelected(Request $request)
    {
        $ids = $request->get('ids',[]);
        $this->repository->deleteByIds($ids);
        return $this->sendResponse($ids, trans('api.success'));   
    }    
}
