<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Repositories\Criteria\CustomDataTableCriteria;
use App\Repositories\Criteria\UsuariosCriteria;
use App\Repositories\UsersRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

class UserController extends AppBaseController
{
    private $repository;

    public function __construct(UsersRepository $repo)
    {
        $this->repository = $repo;
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

        $this->repository->pushCriteria(new CustomDataTableCriteria($request));
        $this->repository->pushCriteria(new UsuariosCriteria($request));
        $this->repository->pushCriteria(new RequestCriteria($request));

        $list = $this->repository->with([
            'roles' => function($query) {
                $query->select('id','name');
            },
            'areas' => function($query) {
                $query->select('id','area_id','user_id','responsable');
            },
            'areas.area' => function($query) {
                $query->select('id','nombre');
            }
        ])->scopeQuery(function($query){
            return $query->whereVisible(true);
        })->paginate($request->get('per_page',10));

        return $this->sendResponse($list->toArray(), 'La operacion finalizo con exito');
    }

    /**
     * Store a newly created AccionesControladas in storage.
     * POST /roles
     *
     * @param CreateAccionesControladasAPIRequest $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validation($request);

        $input = $request->except('role','roles','password','areas','_areas');
        
        if ($request->has('password'))
        {
            $input['password'] = Hash::make($request->get('password'));    
        }

        $role = $request->get('role');
        $_areas = $request->get('_areas',[]);

        $model = $this->repository->create($input);
        $model->syncRoles([$role['name']]);
        
        $areas = [];
        foreach ($_areas as $item) 
        {
            if ($item['miembro']) {
                $areas[] = [
                    'area_id' => $item['area_id'],
                    'user_id' => $model->id,
                    'responsable' => $item['responsable']
                ];
            }
        }
        $model->areas()->createMany($areas);


        return $this->sendResponse($model, 'La operacion finalizo con exito');
    }

    public function user(Request $request) 
    {
        $user = $request->user()->with(['roles.permissions' => function($query) {
            $query->select('id','name');
        },
        'areas' => function($query) {
            $query->select('id','area_id','responsable','user_id');
        },        
        'areas.area' => function($query) {
            $query->select('id','nombre');
        }])->whereId($request->user()->id)->first();

        return $user;
    }

    public function show($id)
    {
        /** @var AccionesControladas $roles */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError('No se encontro el registro');
        }

        return $this->sendResponse($model->toArray(), 'La operacion finalizo con exito');
    }

    public function update($id, Request $request)
    {
        $this->validation($request);

        $input = $request->except('role','roles','password','_areas','areas');

        if ($request->has('password') && $request->get('password',null) !== null )
        {
            $input['password'] = Hash::make($request->get('password'));    
        }

        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError('No se encontro el registro');
        }

        $model = $this->repository->update($input, $id);

        if ($request->has('role')) {
            $role = $request->get('role');
            $model->syncRoles([$role['name']]);
        }

        if ($request->has('_areas')) {
            $_areas = $request->get('_areas',[]);    
            $areas = [];
            foreach ($_areas as $item) 
            {
                if ($item['miembro']) {
                    $areas[] = [
                        'area_id' => $item['area_id'],
                        'user_id' => $model->id,
                        'responsable' => $item['responsable']
                    ];
                }
            }
            $model->areas()->delete();
            $model->areas()->createMany($areas);
        }

        return $this->sendResponse($model->toArray(), 'La operacion finalizo con exito');
    }

    public function updateProfile($id, Request $request) 
    {
        $input = $request->except('password');

        if ($request->has('password') && $request->get('password','') !== '' )
        {
            $input['password'] = Hash::make($request->get('password'));    
        }

        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError('No se encontro el registro');
        }

        $model = $this->repository->update($input, $id);

        return $this->sendResponse($model->toArray(), 'La operacion finalizo con exito');

    }

    public function destroy($id)
    {
        /** @var AccionesControladas $roles */
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError('Row not found');
        }

        $model->delete();

        return $this->sendResponse($id, 'Row deleted successfully');
    }

    public function removeSelected(Request $request)
    {
        $ids = $request->get('ids',[]);
        $this->repository->deleteByIds($ids);
        return $this->sendResponse($ids, 'Row deleted successfully');   
    }

    public function resetPassword ($id,Request $request)
    {
        $model = $this->repository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError('No se encontro el registro');
        }

        $password = \Illuminate\Support\Str::random(6);

        $model->password = \Hash::make($password);
        $model->save();

        return $this->sendResponse(['password' => $password],'La nueva clave generada es '.$password);
    }

    public function updatePassword(Request $request)
    {
        $rules = [
            'new_password'          =>  'required',
            'confirm_new_password'  =>  'required|same:new_password'
        ];

        $this->validate($request, $rules);

        $user = $request->user();
        $user->password = bcrypt($request->input('new_password'));
        $user->saveOrFail();

        return response()->json(compact('user'));
    }

    protected function validation($request) 
    {
         $rules = [
            'name'      => 'required',
            'username'  => 'required|unique:users,username,'.$request->get('id').',id',
            'email'     =>  'required|email|unique:users,email,'.$request->get('id').',id',
            'password'  =>  'required',
            'role'      => 'required'
        ];

        if ($request->get('id',0) > 0) {
            unset($rules['password']);
        }

        $this->validate($request, $rules);
    }

}