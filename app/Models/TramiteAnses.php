<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TramiteBeneficio
 * @package App\Models
 * @version April 11, 2018, 6:23 pm UTC
 *
 * @property integer tramite_id
 * @property string haber_mensual
 * @property string retroactivo
 * @property string mes_alta
 * @property date fecha_cobro
 * @property string agente_pagador
 */
class TramiteAnses extends Model
{
    use SoftDeletes;

    public $table = 'tramite_anses';
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'tramite_id',
        'estado_anses_id',
        'fecha_remision',
        'fecha_remision_vto',
        'observations'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'tramite_id' => 'integer',
        'estado_anses_id' => 'integer',
        'fecha_remision' => 'date',
        'fecha_remision_vto' => 'date',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'tramite_id' => 'required'
    ];   

    public $appends = ['fecha_remision_dp'];

    public function getFechaRemisionDpAttribute($value)
    {
        return ($this->attributes['fecha_remision'] ? \Carbon\Carbon::createFromFormat('Y-m-d',$this->attributes['fecha_remision'])->toIso8601String() : "");
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

    public function tramite() 
    {
    	return $this->belongsTo('App\Models\TramiteCliente','tramite_id');
    }

    public function estado() 
    {
        return $this->belongsTo('App\Models\EstadoAnses','estado_anses_id');
    }      

    public function ultimoEstadio()
    {
        return $this->hasOne('App\Models\TramiteAnses')->latest('fecha_remision');
    }
}
