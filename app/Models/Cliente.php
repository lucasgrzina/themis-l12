<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Cliente
 * @package App\Models
 * @version February 27, 2018, 5:08 pm UTC
 *
 * @property char personeria
 * @property date fecha_entrevista
 * @property string nombre_completo
 * @property string cuit
 * @property char sexo
 * @property integer tipo_doc_id
 * @property string nro_doc
 * @property char nacionalidad
 * @property date fecha_ing_pais
 * @property date fecha_nac
 * @property integer pais_id
 * @property string cp
 * @property string localidad
 * @property integer provincia_id
 * @property string clp_extranjero
 * @property string direccion
 * @property string emails
 * @property string telefonos
 * @property char categoria
 * @property integer empresa_referencia_id
 * @property char estado_civil
 * @property string nombre_conyuge
 * @property string apellido_conyuge
 * @property integer tipo_doc_conyuge_id
 * @property string nro_doc_conyuge
 * @property string email_conyuge
 * @property string telefono_conyuge
 */
class Cliente extends Model
{
    use SoftDeletes;

    public $table = 'clientes';
    

    protected $dates = ['deleted_at','fecha_entrevista','fecha_nac','fecha_ing_pais','fecha_vto_directorio'];


    public $fillable = [
        'personeria',
        'fecha_entrevista',
        'nombre_completo',
        'cuit',
        'sexo',
        'tipo_doc_id',
        'nro_doc',
        'nacionalidad',
        'fecha_ing_pais',
        'fecha_nac',
        'pais_id',
        'cp',
        'localidad',
        'provincia_id',
        'clp_extranjero',
        'direccion',
        'emails',
        'telefonos',
        'categoria',
        'empresa_referencia_id',
        'estado_civil',
        'nombre_conyuge',
        'apellido_conyuge',
        'tipo_doc_conyuge_id',
        'nro_doc_conyuge',
        'email_conyuge',
        'telefono_conyuge',
        'ubicacion_carpeta',
        'tipo_aporte_id',
        'tipo_sociedad_id',
        'sede_social',
        'nombre_rep_legal',
        'directorio',
        'fecha_vto_directorio',
        'libros_estudio',
        'dom_fiscal',
        'dom_legal',
        'actividad',
        'facultades',
        'cond_iva_id',
        'fecha_casamiento',
        'fecha_enviudez',
        'hijos_comun',
        'anios_convivencia',
        'nro_correlativo',
        'clave_seguridad_social',
        'clave_fiscal',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'personeria' => 'string',
        'fecha_entrevista' => 'date',
        'nombre_completo' => 'string',
        'cuit' => 'string',
        'sexo' => 'string',
        'tipo_doc_id' => 'integer',
        'nro_doc' => 'string',
        'nacionalidad' => 'string',
        'fecha_ing_pais' => 'date',
        'fecha_nac' => 'date',
        'pais_id' => 'integer',
        'cp' => 'string',
        'localidad' => 'string',
        'provincia_id' => 'integer',
        'clp_extranjero' => 'string',
        'direccion' => 'string',
        'emails' => 'array',
        'telefonos' => 'array',
        'categoria' => 'string',
        'empresa_referencia_id' => 'integer',
        'estado_civil' => 'string',
        'nombre_conyuge' => 'string',
        'apellido_conyuge' => 'string',
        'tipo_doc_conyuge_id' => 'integer',
        'nro_doc_conyuge' => 'string',
        'email_conyuge' => 'string',
        'telefono_conyuge' => 'string',
        'ubicacion_carpeta' => 'string',
        'tipo_aporte_id' => 'integer',
        'tipo_sociedad_id' => 'integer',

        'sede_social' => 'string',
        'nombre_rep_legal' => 'string',
        'directorio' => 'string',
        'fecha_vto_directorio' => 'date',
        'libros_estudio' => 'boolean',
        'dom_fiscal' => 'string',
        'dom_legal' => 'string',
        'actividad' => 'string',
        'facultades' => 'string',  
        'cond_iva_id' => 'integer',

        'fecha_casamiento' => 'date',
        'fecha_enviudez' => 'date',
        'hijos_comun' => 'integer',
        'anios_convivencia' => 'integer',
        'nro_correlativo' => 'string'              
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'personeria' => 'required',
        'nombre_completo' => 'required',
        'sexo' => 'required_if:personeria,==,H',
        'tipo_doc_id' => 'required_if:personeria,==,H',
        'nro_doc' => 'required_if:personeria,==,H',
        'cuit' => 'unique:clientes,cuit,{:id},id'
        //'fecha_ing_pais' => 'required'
    ];

    public function getFechaCasamientoAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d',$value)->format('d/m/Y') : '');
    }
    public function setFechaCasamientoAttribute($value)
    {
        $this->attributes['fecha_casamiento'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    }  

    public function getFechaEnviudezAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d',$value)->format('d/m/Y') : '');
    }
    public function setFechaEnviudezAttribute($value)
    {
        $this->attributes['fecha_enviudez'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    }  

    public function getFechaNacAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$value)->format('d/m/Y') : '');
    }
    public function setFechaNacAttribute($value)
    {
        $this->attributes['fecha_nac'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    }    

    public function getFechaIngPaisAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$value)->format('d/m/Y') : "");
    }
    public function setFechaIngPaisAttribute($value)
    {
        $this->attributes['fecha_ing_pais'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    }    

    public function getFechaEntrevistaAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$value)->format('d/m/Y') : "");
    }
    public function setFechaEntrevistaAttribute($value)
    {
        $this->attributes['fecha_entrevista'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    }  

    public function getFechaVtoDirectorioAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$value)->format('d/m/Y') : "");
    }
    public function setFechaVtoDirectorioAttribute($value)
    {
        $this->attributes['fecha_vto_directorio'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    }  

    public function documentos() 
    {
        return $this->hasMany('App\Models\DocumentoCliente','cliente_id');
    }
 
    public function observaciones() 
    {
        return $this->hasMany('App\Models\ObservacionCliente','cliente_id');
    }

    public function avisos() 
    {
        return $this->hasMany('App\Models\AvisoCliente','cliente_id');
    }    

    public function requerimientos() 
    {
        return $this->hasMany('App\Models\RequerimientoCliente','cliente_id');
    }
 
    public function tramites() 
    {
        return $this->hasMany('App\Models\TramiteCliente','cliente_id');
    }
    public function tramitesActuales() 
    {
        return $this->hasMany('App\Models\TramiteCliente','cliente_id')->actuales();
    }    
    public function tramitesHistoricos() 
    {
        return $this->hasMany('App\Models\TramiteCliente','cliente_id')->historicos();
    }
    public function provincia() 
    {
        return $this->belongsTo('App\Models\Provincia','provincia_id');
    }
    public function horas() 
    {
        return $this->hasMany('App\Models\TimeHora','cliente_id');
    }    

   
}
