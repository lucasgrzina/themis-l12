<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Mail\AltaClienteAviso;
use App\Mail\NuevoCliente;
use App\Models\Area;
use App\Models\CatalogoOpcion;
use App\Models\Cliente;
use App\Repositories\TipoDocumentoRepository;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Wizard de alta rapida de clientes.
 *
 * store(): en UNA transaccion crea el cliente (datos minimos), la observacion inicial,
 * un aviso por destinatario (usuario) y los documentos (solapa Docs). Recien despues del
 * commit manda un mail por destinatario con los documentos adjuntos.
 *
 * Los archivos se suben antes con POST upload/docs (quedan en uploads/tmp) y aca solo se
 * recibe el nombre; DocumentoClienteObserver los mueve a uploads/docs al crear el documento.
 */
class AltaRapidaClienteAPIController extends AppBaseController
{
    /** GET combos/alta-rapida */
    public function combos(TipoDocumentoRepository $tipoDocRepo)
    {
        $usuarios = User::with('areas')->whereVisible(true)->orderBy('name')->get()
            ->map(function ($u) {
                return [
                    'id'       => $u->id,
                    'name'     => $u->name,
                    'area_ids' => $u->areas->pluck('area_id')->values()->all(),
                ];
            })->values()->all();

        return $this->sendResponse([
            'tipo_doc'        => $tipoDocRepo->orderBy('nombre')->all(['id', 'nombre'])->toArray(),
            'areas'           => Area::orderBy('nombre')->get(['id', 'nombre'])->toArray(),
            'usuarios'        => $usuarios,
            'motivos'         => CatalogoOpcion::combo('motivo_aviso'),
            'tipos_contacto'  => CatalogoOpcion::combo('tipo_contacto'),
            'tipos_documento' => CatalogoOpcion::combo('tipo_documento'),
            'limites'         => [
                'max_adjuntos'    => config('themis.alta_rapida.max_adjuntos'),
                'max_adjuntos_mb' => config('themis.alta_rapida.max_adjuntos_mb'),
            ],
        ], trans('api.success'));
    }

    /** POST clientes/alta-rapida */
    public function store(Request $request)
    {
        $data = $request->all();

        $error = $this->validar($data);
        if ($error) {
            return $this->sendError($error, 400);
        }

        $creador = $request->user();
        $c = $data['cliente'];
        $observacion = trim((string) data_get($data, 'observacion', ''));
        $destinatarios = collect(data_get($data, 'destinatarios', []))->map('intval')->unique()->values();
        $documentos = data_get($data, 'documentos', []);

        try {
            $resultado = DB::transaction(function () use ($c, $observacion, $destinatarios, $documentos, $data, $creador) {
                $cliente = Cliente::create($this->datosCliente($c));

                if ($observacion !== '') {
                    $cliente->observaciones()->create([
                        'user_id'     => $creador->id,
                        'observacion' => $observacion,
                    ]);
                }

                $docs = collect();
                foreach ($documentos as $d) {
                    $docs->push($cliente->documentos()->create([
                        'nombre'         => $d['nombre'],
                        'nombre_archivo' => $d['nombre_archivo'],
                        'nombre_real'    => data_get($d, 'nombre_real'),
                        'fecha_archivo'  => date('d/m/Y'),
                    ]));
                }

                $avisos = collect();
                foreach ($destinatarios as $userId) {
                    $avisos->push($cliente->avisos()->create([
                        'user_id' => $creador->id,
                        'type'    => 'U',
                        'type_id' => $userId,
                        'motivo'  => trim($data['aviso']['motivo']),
                        'fecha'   => $data['aviso']['fecha'],
                    ]));
                }

                return compact('cliente', 'docs', 'avisos');
            });
        } catch (\Exception $e) {
            Log::error('Alta rapida de cliente: ' . $e->getMessage());
            $this->devolverArchivosATmp($documentos);
            return $this->sendError('No se pudo completar el alta del cliente. No se guardó ningún dato.', 500);
        }

        $cliente = $resultado['cliente'];
        $mails = $this->enviarMails($cliente, $resultado['avisos'], $resultado['docs'], $creador, $observacion);

        $out = $cliente->toArray();
        $out['alta_rapida'] = [
            'avisos'     => $resultado['avisos']->count(),
            'documentos' => $resultado['docs']->count(),
            'mails'      => $mails,
        ];

        return $this->sendResponse($out, trans('api.success'));
    }

    /** Devuelve el primer mensaje de error (string) o null si todo es valido. */
    private function validar(array $data)
    {
        $v = Validator::make($data, [
            'cliente'                  => 'required|array',
            'cliente.personeria'       => 'required|in:H,J',
            'cliente.nombre_completo'  => 'required|max:150',
            'cliente.cuit'             => 'nullable|max:20|unique:clientes,cuit',
            'cliente.tipo_doc_id'      => 'required_without:cliente.cuit',
            'cliente.nro_doc'          => 'required_without:cliente.cuit|max:20',
            'cliente.fecha_entrevista' => 'required|date_format:d/m/Y',
            'observacion'              => 'nullable|string',
            'destinatarios'            => 'nullable|array',
            'destinatarios.*'          => 'integer|exists:users,id',
            'aviso.motivo'             => 'required_with:destinatarios.0',
            'aviso.fecha'              => 'required_with:destinatarios.0',
            'documentos'               => 'nullable|array',
            'documentos.*.nombre'      => 'required',
            'documentos.*.nombre_archivo' => 'required',
        ], [
            'cliente.nombre_completo.required'  => 'Ingrese el nombre o razón social.',
            'cliente.cuit.unique'               => 'Ya existe un cliente con ese CUIT/CUIL.',
            'cliente.tipo_doc_id.required_without' => 'Ingrese el CUIT/CUIL o, si no tiene, el tipo y número de documento.',
            'cliente.nro_doc.required_without'  => 'Ingrese el CUIT/CUIL o, si no tiene, el tipo y número de documento.',
            'cliente.fecha_entrevista.required' => 'Ingrese la fecha de entrevista.',
            'cliente.fecha_entrevista.date_format' => 'La fecha de entrevista debe tener formato DD/MM/AAAA.',
            'aviso.motivo.required_with'        => 'Ingrese el motivo del aviso.',
            'aviso.fecha.required_with'         => 'Ingrese la fecha del aviso.',
            'destinatarios.*.exists'            => 'Alguno de los destinatarios ya no existe.',
        ]);

        if ($v->fails()) {
            return $v->errors()->first();
        }

        $c = $data['cliente'];
        $tels = array_filter((array) data_get($c, 'telefonos', []), function ($i) { return !empty($i['val']); });
        $mails = array_filter((array) data_get($c, 'emails', []), function ($i) { return !empty($i['val']); });
        if (count($tels) + count($mails) < 1) {
            return 'Ingrese al menos un teléfono o un e-mail.';
        }
        foreach ($mails as $m) {
            if (!filter_var($m['val'], FILTER_VALIDATE_EMAIL)) {
                return 'El e-mail "' . $m['val'] . '" no es válido.';
            }
        }

        if (!empty($data['destinatarios']) && !\DateTime::createFromFormat('Y-m-d\TH:i:s.uO', $data['aviso']['fecha'])) {
            return 'La fecha del aviso no es válida.';
        }

        return $this->validarAdjuntos((array) data_get($data, 'documentos', []));
    }

    private function validarAdjuntos(array $documentos)
    {
        $max = (int) config('themis.alta_rapida.max_adjuntos');
        $maxMb = (int) config('themis.alta_rapida.max_adjuntos_mb');
        if (count($documentos) > $max) {
            return "Se pueden adjuntar hasta $max archivos.";
        }

        $disk = Storage::disk('uploads');
        $total = 0;
        foreach ($documentos as $d) {
            $name = (string) $d['nombre_archivo'];
            // El nombre viene de upload/docs: tiene que ser un archivo suelto de uploads/tmp.
            if ($name === '' || $name !== basename($name) || !$disk->exists('tmp/' . $name)) {
                return 'El archivo "' . data_get($d, 'nombre_real', $name) . '" ya no está disponible, vuelva a adjuntarlo.';
            }
            $total += $disk->size('tmp/' . $name);
        }
        if ($total > $maxMb * 1024 * 1024) {
            return "Los adjuntos superan el máximo de $maxMb MB en total.";
        }

        return null;
    }

    /**
     * DocumentoClienteObserver mueve tmp -> docs al crear cada documento, pero eso no entra en la
     * transaccion de la base. Si el alta se revierte, se devuelven los archivos a tmp para que el
     * usuario pueda reintentar sin volver a subirlos y no queden huerfanos en docs.
     */
    private function devolverArchivosATmp(array $documentos)
    {
        $disk = Storage::disk('uploads');
        foreach ($documentos as $d) {
            $name = (string) data_get($d, 'nombre_archivo');
            try {
                if ($name !== '' && $name === basename($name) && $disk->exists('docs/' . $name) && !$disk->exists('tmp/' . $name)) {
                    $disk->move('docs/' . $name, 'tmp/' . $name);
                }
            } catch (\Exception $e) {
                Log::error('Alta rapida: no se pudo devolver a tmp ' . $name . ': ' . $e->getMessage());
            }
        }
    }

    /** Campos del cliente que acepta la carga rapida (el resto queda para completar la ficha). */
    private function datosCliente(array $c)
    {
        $limpiar = function ($items) {
            return array_values(array_filter((array) $items, function ($i) { return !empty($i['val']); }));
        };

        return [
            'personeria'       => $c['personeria'],
            'nombre_completo'  => trim($c['nombre_completo']),
            'cuit'             => data_get($c, 'cuit') ?: null,
            'tipo_doc_id'      => data_get($c, 'tipo_doc_id') ?: null,
            'nro_doc'          => data_get($c, 'nro_doc') ?: null,
            'fecha_entrevista' => $c['fecha_entrevista'],
            'telefonos'        => $limpiar(data_get($c, 'telefonos')),
            'emails'           => $limpiar(data_get($c, 'emails')),
            'pais_id'          => config('themis.alta_rapida.pais_default_id'),
            'categoria'        => $c['personeria'] === 'H' ? 'P' : null,
        ];
    }

    /**
     * Mails posteriores al commit. Un fallo de mail nunca revierte el alta: se loguea y se informa.
     * @return array ['enviados' => n, 'fallidos' => n, 'habilitado' => bool]
     */
    private function enviarMails(Cliente $cliente, $avisos, $docs, User $creador, $observacion)
    {
        $res = ['habilitado' => true, 'enviados' => 0, 'fallidos' => 0];

        if ($res['habilitado']) {
            foreach ($avisos as $aviso) {
                try {
                    $dest = User::find($aviso->type_id);
                    if ($dest && $dest->email) {
                        Mail::queue(new AltaClienteAviso($cliente, $aviso, $dest, $creador, $observacion, $docs));
                        $res['enviados']++;
                    }
                } catch (\Exception $e) {
                    $res['fallidos']++;
                    Log::error('Alta rapida: error enviando mail al usuario ' . $aviso->type_id . ': ' . $e->getMessage());
                }
            }
        }

        // Mismo comportamiento que el alta comun: mail "Nuevo cliente" a los emails del propio cliente.
        try {
            if (count((array) $cliente->emails) > 0) {
                Mail::queue(new NuevoCliente($cliente));
            }
        } catch (\Exception $e) {
            Log::error('Alta rapida: error enviando NuevoCliente: ' . $e->getMessage());
        }

        return $res;
    }
}
