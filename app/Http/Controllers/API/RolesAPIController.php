<?php

namespace App\Http\Controllers\API;

//use App\Http\Requests\API\CreateAccionesControladasAPIRequest;
//use App\Http\Requests\API\UpdateAccionesControladasAPIRequest;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CURolesAPIRequest;
use App\Repositories\Criteria\CustomDataTableCriteria;
use App\Repositories\RolesRepository;
use Illuminate\Http\Request;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;
use Spatie\Permission\Models\Role;

/**
 * Class AccionesControladasController
 * @package App\Http\Controllers\API
 */

class RolesAPIController extends AppBaseController
{
    /** @var  RolesRepository */
    private $rolesRepository;

    public function __construct(RolesRepository $rolesRepo)
    {
        $this->rolesRepository = $rolesRepo;
    }

    /**
     * Display a listing of the AccionesControladas.
     * GET|HEAD /roles
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->rolesRepository->pushCriteria(new CustomDataTableCriteria($request));
        $this->rolesRepository->pushCriteria(new RequestCriteria($request));
        $roles = $this->rolesRepository->with(['permissions' => function($query) {
            $query->select('id','name');
        }])->paginate($request->get('per_page',10));

        return $this->sendResponse($roles->toArray(), 'La operacion finalizo con exito');
    }

    /**
     * Store a newly created AccionesControladas in storage.
     * POST /roles
     *
     * @param CreateAccionesControladasAPIRequest $request
     *
     * @return Response
     */
    public function store(CURolesAPIRequest $request)
    {
        $input = $request->except('permissions');

        $roles = $this->rolesRepository->create($input);

        return $this->sendResponse($roles->toArray(), 'Acciones Controladas saved successfully');
    }

    /**
     * Display the specified AccionesControladas.
     * GET|HEAD /roles/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var AccionesControladas $roles */
        $roles = $this->rolesRepository->findWithoutFail($id);

        if (empty($roles)) {
            return $this->sendError('Acciones Controladas not found');
        }

        return $this->sendResponse($roles->toArray(), 'Acciones Controladas retrieved successfully');
    }

    /**
     * Update the specified AccionesControladas in storage.
     * PUT/PATCH /roles/{id}
     *
     * @param  int $id
     * @param UpdateAccionesControladasAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CURolesAPIRequest $request)
    {
        $input = $request->except('permissions');

        /** @var AccionesControladas $roles */
        $roles = $this->rolesRepository->findWithoutFail($id);

        if (empty($roles)) {
            return $this->sendError('Acciones Controladas not found');
        }

        $roles = $this->rolesRepository->update($input, $id);

        //$roles->permissions()->delete();
        $roles->syncPermissions($request->get('permissions'));

        return $this->sendResponse($roles->toArray(), 'AccionesControladas updated successfully');
    }

    /**
     * Remove the specified AccionesControladas from storage.
     * DELETE /roles/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var AccionesControladas $roles */
        $roles = $this->rolesRepository->findWithoutFail($id);

        if (empty($roles)) {
            return $this->sendError('Row not found');
        }

        $roles->delete();

        return $this->sendResponse($id, 'Row deleted successfully');
    }

    public function removeSelected(Request $request)
    {
        $ids = $request->get('ids',[]);
        $this->rolesRepository->deleteByIds($ids);
        return $this->sendResponse($ids, 'Row deleted successfully');   
    }

    public function full() 
    {
        $roles = $this->rolesRepository->with(['permissions' => function($query) {
            $query->select('id','name','display_name');
        }])->all();
        
        return response()->json($roles);

    }
}
