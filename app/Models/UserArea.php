<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserArea extends Model
{
    //
    public $table = 'user_area';

    public $fillable = [
        'area_id',
        'user_id',
        'responsable'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'area_id' => 'integer',
        'user_id' => 'integer',
        'responsable' => 'boolean'
    ];


    public function usuario() {
    	return $this->belongsTo('App\User');
    }

    public function area() {
    	return $this->belongsTo('App\Models\Area');
    }    
}
