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
        ])->orderBy('fecha','asc')->orderBy('id','asc')->paginate($request->get('per_page',50));

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
        $input = $request->except(['gestion','cliente','usuario','estado','inicio','fin','hora_inicio','hora_fin']);

        try
        {
            DB::beginTransaction();
            $input['fecha'] = Carbon::createFromFormat('Y-m-d',\Illuminate\Support\Str::limit($input['fecha_dp'],10,''))->format('Y-m-d');
            $input['user_id'] = request()->user()->id;
            $input['estado'] = 'vinculada';
            $input = array_merge($input, $this->inicioFin($input['fecha'], $request->get('hora_inicio'), $request->get('hora_fin')));
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
        $input = $request->except(['gestion','cliente','usuario','estado','inicio','fin','hora_inicio','hora_fin']);

        try
        {
            DB::beginTransaction();        
            $input['fecha'] = Carbon::createFromFormat('Y-m-d',\Illuminate\Support\Str::limit($input['fecha_dp'],10,''))->format('Y-m-d');
            /** @var TimeHora $model */
            $model = $this->repository->findWithoutFail($id);

            if (empty($model)) {
                throw new \Exception(trans('api.not_found'), 1);
            }

            $input['estado'] = 'vinculada';
            $input = array_merge($input, $this->inicioFin($input['fecha'], $request->get('hora_inicio'), $request->get('hora_fin')));
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

    /**
     * Timer en curso del usuario + cantidad de cargas sin vincular.
     */
    public function timerActual()
    {
        $user = request()->user();
        $autocerrado = $this->autocerrarOlvidado($user->id);
        $timer = TimeHora::where('user_id', $user->id)->where('estado', 'corriendo')->first();
        $esAdmin = $user->time_level === 5;

        return $this->sendResponse([
            'timer' => $timer ? $timer->toArray() : null,
            'pendientes' => TimeHora::where('user_id', $user->id)->where('estado', 'pendiente')->count(),
            'pendientes_equipo' => $esAdmin ? TimeHora::where('estado', 'pendiente')->count() : null,
            'ahora' => Carbon::now()->toIso8601String(),
            'aviso_horas' => config('themis.time.timer_aviso_horas'),
            'autocerrado' => $autocerrado,
        ], trans('api.success'));
    }

    /**
     * Pendientes de vincular agrupados por usuario (solo administradores).
     */
    public function pendientesResumen()
    {
        if (request()->user()->time_level !== 5) {
            return $this->sendError('No autorizado', 403);
        }

        $rows = TimeHora::where('estado', 'pendiente')
            ->join('users', 'users.id', '=', 'time_horas.user_id')
            ->groupBy('time_horas.user_id', 'users.name')
            ->orderBy('users.name')
            ->get([
                'time_horas.user_id',
                'users.name',
                DB::raw('count(*) as cantidad'),
                DB::raw('sum(time_horas.minutos) as minutos'),
                DB::raw('min(time_horas.fecha) as mas_antigua'),
            ]);

        return $this->sendResponse($rows->toArray(), trans('api.success'));
    }

    /**
     * Inicia un timer. Si ya hay uno corriendo, lo cierra (queda pendiente).
     */
    public function timerIniciar(Request $request)
    {
        try
        {
            DB::beginTransaction();
            $user = request()->user();

            $corriendo = TimeHora::where('user_id', $user->id)->where('estado', 'corriendo')->first();
            if ($corriendo) {
                $this->cerrarTimer($corriendo, []);
            }

            $ahora = Carbon::now();
            TimeHora::create([
                'user_id' => $user->id,
                'fecha' => $ahora->format('Y-m-d'),
                'minutos' => 0,
                'estado' => 'corriendo',
                'inicio' => $ahora,
                'descripcion' => $request->get('descripcion'),
            ]);
            DB::commit();
        }
        catch(\Exception $e)
        {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }

        return $this->timerActual();
    }

    /**
     * Termina el timer en curso. Si llegan cliente, gestion y referencia queda
     * vinculada; si no, queda pendiente de vincular.
     */
    public function timerTerminar(Request $request)
    {
        try
        {
            DB::beginTransaction();
            $timer = TimeHora::where('user_id', request()->user()->id)->where('estado', 'corriendo')->first();
            if (!$timer) {
                throw new \Exception('No hay un timer en curso');
            }
            $this->cerrarTimer($timer, $request->only(['cliente_id', 'gestion_id', 'referencia', 'descripcion']));
            DB::commit();
        }
        catch(\Exception $e)
        {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }

        return $this->timerActual();
    }

    public function timerDescartar()
    {
        TimeHora::where('user_id', request()->user()->id)->where('estado', 'corriendo')->delete();
        return $this->timerActual();
    }

    /**
     * Vincula una carga pendiente a cliente/gestion/referencia.
     */
    public function vincular($id, Request $request)
    {
        $this->validate($request, [
            'cliente_id' => 'required',
            'gestion_id' => 'required',
            'referencia' => 'required',
        ]);

        $model = TimeHora::where('user_id', request()->user()->id)->where('estado', 'pendiente')->find($id);
        if (!$model) {
            return $this->sendError(trans('api.not_found'));
        }

        $model->fill($request->only(['cliente_id', 'gestion_id', 'referencia', 'descripcion']));
        $model->estado = 'vinculada';
        $model->save();

        return $this->sendResponse($model->toArray(), trans('api.success'));
    }

    private function cerrarTimer(TimeHora $timer, array $datos, $fin = null)
    {
        $fin = $fin ?: Carbon::now();
        $segundos = max(0, $fin->diffInSeconds($timer->inicio, true));
        // Redondeo hacia arriba a multiplos de 5, minimo 5
        $minutos = max(5, (int) (ceil($segundos / 60 / 5) * 5));

        $timer->fill(array_filter($datos, function ($v) { return $v !== null && $v !== ''; }));
        $timer->fin = $fin;
        $timer->minutos = $minutos;
        $timer->estado = ($timer->cliente_id && $timer->gestion_id && $timer->referencia) ? 'vinculada' : 'pendiente';
        $timer->save();
    }

    /**
     * Cierra el timer si lleva mas de timer_max_horas corriendo (se olvido de terminarlo).
     */
    private function autocerrarOlvidado($userId)
    {
        $timer = TimeHora::where('user_id', $userId)->where('estado', 'corriendo')->first();
        $max = (float) config('themis.time.timer_max_horas');
        if (!$timer || $max <= 0) {
            return false;
        }

        $limite = $timer->inicio->copy()->addMinutes((int) round($max * 60));
        if ($limite->isFuture()) {
            return false;
        }

        $timer->descripcion = trim($timer->descripcion . ' [Cerrado automaticamente: timer sin terminar]');
        $this->cerrarTimer($timer, [], $limite);
        return true;
    }

    private function inicioFin($fecha, $horaInicio, $horaFin)
    {
        if (!$horaInicio || !$horaFin) {
            return ['inicio' => null, 'fin' => null];
        }

        // La hora se ingresa en horario local (config themis.time.tz); se convierte
        // al instante UTC que se almacena, para que al mostrarla vuelva a verse igual.
        $tz = config('themis.time.tz', 'America/Argentina/Buenos_Aires');

        return [
            'inicio' => Carbon::createFromFormat('Y-m-d H:i', $fecha . ' ' . $horaInicio, $tz)->setTimezone('UTC'),
            'fin' => Carbon::createFromFormat('Y-m-d H:i', $fecha . ' ' . $horaFin, $tz)->setTimezone('UTC'),
        ];
    }
}
