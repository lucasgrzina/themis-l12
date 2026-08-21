<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RequerimientoCliente
 * @package App\Models
 * @version March 16, 2018, 2:17 pm UTC
 *
 * @property integer area_id
 * @property integer cliente_id
 * @property integer colega_id
 * @property integer tipo_tramite_id
 * @property string recomendado
 * @property string nombre_causante
 * @property integer tipo_doc_id_causante
 * @property string nro_doc_causante
 * @property string domicilio_causante
 * @property string estado_civil
 * @property date fecha_mat_causante
 * @property date fecha_conv_causante
 * @property date fecha_fallecimiento_causante
 * @property boolean hijos
 * @property integer req_nec_id
 * @property integer estado_req_id
 * @property integer tramite_id
 * @property date fecha_turno
 * @property string hora_turno
 * @property integer rep_origen_id
 * @property string documentacion
 */
class RequerimientoCliente extends Model
{
    use SoftDeletes;

    public $table = 'requerimiento_clientes';
    

    protected $dates = ['deleted_at','fecha_mat_causante','fecha_fallecimiento_causante','fecha_turno','fecha_conv_causante'];


    public $fillable = [
        'area_id',
        'cliente_id',
        'colega_id',
        'tipo_tramite_id',
        'recomendado',
        'nombre_causante',
        'tipo_doc_id_causante',
        'nro_doc_causante',
        'domicilio_causante',
        'estado_civil',
        'fecha_mat_causante',
        'fecha_conv_causante',
        'fecha_fallecimiento_causante',
        'hijos',
        'req_nec_id',
        'estado_req_id',
        'tramite_id',
        'fecha_turno',
        'hora_turno',
        'rep_origen_id',
        'documentacion',
        'autos',
        'parte'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'area_id' => 'integer',
        'cliente_id' => 'integer',
        'colega_id' => 'integer',
        'tipo_tramite_id' => 'integer',
        'recomendado' => 'string',
        'nombre_causante' => 'string',
        'tipo_doc_id_causante' => 'integer',
        'nro_doc_causante' => 'string',
        'domicilio_causante' => 'string',
        'estado_civil' => 'string',
        'fecha_mat_causante' => 'date',
        'fecha_conv_causante' => 'date',
        'fecha_fallecimiento_causante' => 'date',
        'hijos' => 'boolean',
        'req_nec_id' => 'integer',
        'estado_req_id' => 'integer',
        'tramite_id' => 'integer',
        'fecha_turno' => 'date',
        'hora_turno' => 'string',
        'rep_origen_id' => 'integer',
        'documentacion' => 'array',
        'autos' => 'string',
        'parte' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'area_id' => 'required',
        'cliente_id' => 'required',
        'colega_id' => 'required',
        'tipo_tramite_id' => 'required'
    ];

    public $appends = ['nombre_parte'];

    public static $partes = [
        'A' => 'Actora',
        'D' => 'Demandada',
        'T' => 'Tercero citado'
    ];

    public static function getComboPartes()
    {
        $partes = [];
        $partes[] = ['id' => NULL, 'nombre' => 'Seleccione'];
        foreach (self::$partes as $key => $value) {
            $partes[] = ['id' => $key, 'nombre' => $value];
        }
        return $partes;
    }

    public function getNombreParteAttribute($value)
    {
        if (array_key_exists($this->parte, self::$partes))
        {
            return self::$partes[$this->parte];
        }
        return NULL;
    }

    public function getDocumentacionAttribute($value)
    {
        return is_null($value) ? [] : json_decode($value);
    }

    public function getFechaMatCausanteAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$value)->format('d/m/Y') : "");
    }
    public function setFechaMatCausanteAttribute($value)
    {
        $this->attributes['fecha_mat_causante'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    }

    public function getFechaFallecimientoCausanteAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$value)->format('d/m/Y') : "");
    }
    public function setFechaFallecimientoCausanteAttribute($value)
    {
        $this->attributes['fecha_fallecimiento_causante'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    }

    public function getFechaConvCausanteAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$value)->format('m/Y') : "");
    }
    public function setFechaConvCausanteAttribute($value)
    {
        $this->attributes['fecha_conv_causante'] = ($value ? \Carbon\Carbon::createFromFormat('m/Y',$value)->format('Y-m-d') : null);
    }  

    public function getFechaTurnoAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$value)->format('d/m/Y') : "");
    }
    public function setFechaTurnoAttribute($value)
    {
        $this->attributes['fecha_turno'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    }      

    public function tipoTramite() 
    {
        return $this->belongsTo('App\Models\TipoTramite','tipo_tramite_id');
    }
    public function estado() 
    {
        return $this->belongsTo('App\Models\EstadoRequerimiento','estado_req_id');
    }
    public function area() 
    {
        return $this->belongsTo('App\Models\Area','area_id');
    }  
    public function repOrigen() 
    {
        return $this->belongsTo('App\Models\ReparticionOrigen','rep_origen_id');
    }        
    public function responsables() 
    {
        return $this->hasMany('App\Models\ResponsableRequerimiento','requerimiento_id');
    }   
    public function colega() 
    {
        return $this->belongsTo('App\Models\Colega','colega_id');
    }
    public function reqNec() 
    {
        return $this->belongsTo('App\Models\RequerimientoCliente','req_nec_id');
    }      
    public function reqPadre() 
    {
        return $this->belongsTo('App\Models\RequerimientoCliente','id','req_nec_id');
    } 
    public function tramite() 
    {
        return $this->belongsTo('App\Models\TramiteCliente','tramite_id');
    }       
    public function cliente() 
    {
        return $this->belongsTo('App\Models\Cliente','cliente_id');
    }      
}
