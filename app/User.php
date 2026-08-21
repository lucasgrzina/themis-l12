<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use HasRoles;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','responsable','username','time_level'
    ];
    protected $casts = [
        'time_level' => 'int'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $appends = ['time_level_desc'];
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
   
    public static function getAreasPorUsuario($id) {
        $areas = [];
        foreach (User::with('areas.area')->find($id)->areas as $value) {
            $areas[] = ['id' => $value->area->id,'nombre' => $value->area->nombre,'responsable' => $value->responsable];
        }        
        return $areas;
    }

    public static function esResponsableDeArea($id,$areaId)
    {
        $count = User::with('areas.area')->find($id)->areas()->whereAreaId($areaId)->whereResponsable(true)->count();

        return $count > 0;
    }

    public function areas() {
        return $this->hasMany('App\Models\UserArea','user_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }    

    public function role () {
         //return $this->hasOne(config('permission.models.role'))->oldest();
        return $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'),
            'model_id',
            'role_id'
        )->take(1);
    }

    public function getTimeLevelDescAttribute($value) 
    {
        $tl = [1 => 'Nivel 1',2 => 'Nivel 2',5 => 'Administrador','' => 'No usa'];
        
        return isset($this->attributes['time_level']) ? $tl[$this->attributes['time_level']] : '';
    }
}
