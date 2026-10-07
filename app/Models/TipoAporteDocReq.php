<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoAporteDocReq extends Model
{
    //
    public $table = 'tipo_aporte_doc_requerida';
    public $timestamps = false;
    public $fillable = [
        'tipo_aporte_id',
        'doc_requerida_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'tipo_aporte_id' => 'integer',
        'doc_requerida_id' => 'integer',
    ];


    public function ta() {
    	return $this->belongsTo('App\Models\TipoAporte');
    }

    public function doc() {
    	return $this->belongsTo('App\Models\DocRequerida','doc_requerida_id');
    }    
}
