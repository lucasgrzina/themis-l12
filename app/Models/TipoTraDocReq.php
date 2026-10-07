<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoTraDocReq extends Model
{
    //
    public $table = 'tipo_tramite_doc_requerida';
    public $timestamps = false;
    public $fillable = [
        'tipo_tramite_id',
        'doc_requerida_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'tipo_tramite_id' => 'integer',
        'doc_requerida_id' => 'integer',
    ];


    public function tt() {
    	return $this->belongsTo('App\Models\TipoTramite');
    }

    public function doc() {
    	return $this->belongsTo('App\Models\DocRequerida','doc_requerida_id');
    }    
}
