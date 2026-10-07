<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TramiteConciliacion
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
class TramiteConciliacion extends Model
{
    use SoftDeletes;

    public $table = 'tramite_conciliacion';
    

    protected $dates = ['deleted_at'];


    public $fillable = [
        'tramite_id',
        'detalle',
        'fecha_cobro',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'tramite_id' => 'integer',
        'detalle' => 'array',
        'fecha_cobro' => 'date',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'tramite_id' => 'required'
    ];   

    public function getFechaCobroAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d',$value)->format('d/m/Y') : "");
    }
    public function setFechaCobroAttribute($value)
    {
        $this->attributes['fecha_cobro'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    }
    public function getCreatedAtAttribute($value)
    {
        return ($value ? \Carbon\Carbon::parse($value)->format('d/m/Y H:i:s') : "");
    }
    public function tramite() 
    {
        return $this->belongsTo('App\Models\TramiteCliente','tramite_id');
    }  
}
