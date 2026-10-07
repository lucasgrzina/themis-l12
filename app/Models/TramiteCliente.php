<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TramiteCliente
 * @package App\Models
 * @version April 9, 2018, 4:01 pm UTC
 *
 * @property integer requerimiento_id
 * @property string expediente
 * @property date fecha_inicio
 * @property integer rep_origen_id
 * @property integer estado_tramite_id
 * @property string secuencia
 * @property integer oficina_ingreso_id
 * @property integer oficina_egreso_id
 * @property boolean resolucion
 * @property integer tipo_resolucion
 * @property string nro_beneficio
 * @property date fecha_beneficio
 * @property string observaciones
 * @property boolean archivar
 * @property date fecha_archivo
 * @property integer usuario_archivo_id
 * @property date fecha_ingreso
 * @property date fecha_egreso
 */
class TramiteCliente extends Model
{
    use SoftDeletes;

    public $table = 'tramite_clientes';
    

    protected $dates = ['deleted_at','fecha_archivo'];


    public $fillable = [
        'area_id',
        'cliente_id',
        'requerimiento_id',
        'expediente',
        'fecha_inicio',
        'rep_origen_id',
        'estado_tramite_id',
        'secuencia',
        'oficina_ingreso_id',
        'oficina_egreso_id',
        'resolucion',
        'tipo_resolucion',
        'nro_beneficio',
        'fecha_beneficio',
        'observaciones',
        'archivar',
        'fecha_archivo',
        'usuario_archivo_id',
        'fecha_ingreso',
        'fecha_egreso',
        'tramite_ant_id',
        'tramite_sig_id',
        'estado_anses_id',
        'fecha_remision',
        'fecha_remision_vto',
        'fecha_vto',
        'deceased',
        'monto_beneficio'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'area_id' => 'integer',
        'requerimiento_id' => 'integer',
        'expediente' => 'string',
        'fecha_inicio' => 'date',
        'rep_origen_id' => 'integer',
        'estado_tramite_id' => 'integer',
        'secuencia' => 'string',
        'oficina_ingreso_id' => 'integer',
        'oficina_egreso_id' => 'integer',
        'resolucion' => 'boolean',
        'tipo_resolucion' => 'integer',
        'nro_beneficio' => 'string',
        'fecha_beneficio' => 'date',
        'observaciones' => 'string',
        'archivar' => 'boolean',
        'fecha_archivo' => 'date',
        'usuario_archivo_id' => 'integer',
        'fecha_ingreso' => 'date',
        'fecha_egreso' => 'date',
        'tramite_ant_id' => 'integer',
        'tramite_sig_id' => 'integer',
        'fecha_vto' => 'date',
        'deceased' => 'boolean',
        'monto_beneficio' => 'string'

    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'requerimiento_id' => 'required',
        'expediente' => 'required',
        'rep_origen_id' => 'required',
        'estado_tramite_id' => 'required',
    ];

    public $appends = ['fecha_inicio_dp'];

    public function getFechaInicioDpAttribute($value)
    {
        return (isset($this->attributes['fecha_inicio']) ? \Carbon\Carbon::createFromFormat('Y-m-d',$this->attributes['fecha_inicio'])->toIso8601String() : "");
    }    

    public function scopeHistoricos($query) {
        return $query->whereArchivar(true);
    }
    public function scopeActuales($query) {
        return $query->where(function($query) {
            $query->whereNull('archivar')->orWhere('archivar',false);
        });
    }

    public function getFechaRemisionAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d',$value)->format('d/m/Y') : "");
    }
    public function setFechaRemisionAttribute($value)
    {
        $this->attributes['fecha_remision'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    } 
    public function getFechaRemisionVtoAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d',$value)->format('d/m/Y') : "");
    }
    public function setFechaRemisionVtoAttribute($value)
    {
        $this->attributes['fecha_remision_vto'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    } 
    public function getFechaVtoAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d',$value)->format('d/m/Y') : "");
    }
    public function setFechaVtoAttribute($value)
    {
        $this->attributes['fecha_vto'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    }     
    public function getFechaInicioAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d',$value)->format('d/m/Y') : "");
    }
    public function setFechaInicioAttribute($value)
    {
        $this->attributes['fecha_inicio'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    }      

    public function getFechaBeneficioAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d',$value)->format('d/m/Y') : "");
    }
    public function setFechaBeneficioAttribute($value)
    {
        $this->attributes['fecha_beneficio'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    }

    public function getFechaIngresoAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d',$value)->format('d/m/Y') : "");
    }
    public function setFechaIngresoAttribute($value)
    {
        $this->attributes['fecha_ingreso'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    }
    public function getFechaEgresoAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d',$value)->format('d/m/Y') : "");
    }
    public function setFechaEgresoAttribute($value)
    {
        $this->attributes['fecha_egreso'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    }

    /*public function getFechaArchivoAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d',$value)->format('d/m/Y') : "");
    }
    public function setFechaArchivoAttribute($value)
    {
        $this->attributes['fecha_archivo'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    }*/

    public function beneficios() {
        return $this->hasMany('App\Models\TramiteBeneficio','tramite_id');
    }
    public function conciliacion() {
        return $this->hasMany('App\Models\TramiteConciliacion','tramite_id');
    }      
    public function anses() {
        return $this->hasMany('App\Models\TramiteAnses','tramite_id');
    }          
    public function ultimoEstadioAnses()
    {
        return $this->hasOne('App\Models\TramiteAnses','tramite_id')->latest('fecha_remision');
    }

    public function estado() 
    {
        return $this->belongsTo('App\Models\EstadoTramite','estado_tramite_id');
    }
  
    public function requerimiento()
    {
        return $this->belongsTo('App\Models\RequerimientoCliente','requerimiento_id');
    }
    public function repOrigen() 
    {
        return $this->belongsTo('App\Models\ReparticionOrigen','rep_origen_id');
    }      
    public function expJudicial() {
        return $this->hasOne('App\Models\TramiteExpediente','tramite_id');
    }      
    public function tramiteSig() {
        return $this->hasOne('App\Models\TramiteCliente','id','tramite_sig_id');
    }      
    public function tramiteAnt() {
        return $this->hasOne('App\Models\TramiteCliente','id','tramite_ant_id');
    }      
    public function cliente() 
    {
        return $this->belongsTo('App\Models\Cliente','cliente_id');
    } 

    public function vencimientos()
    {
        return $this->morphMany('App\Models\Vencimiento', 'vencible');
    }
}
