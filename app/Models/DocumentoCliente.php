<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DocumentoCliente
 * @package App\Models
 * @version March 8, 2018, 2:31 pm UTC
 *
 * @property integer cliente_id
 * @property string nombre
 * @property string descripcion
 * @property string nombre_archivo
 */
class DocumentoCliente extends Model
{
    use SoftDeletes;

    public $table = 'documento_clientes';
    

    protected $dates = ['deleted_at','fecha_archivo'];


    public $fillable = [
        'cliente_id',
        'nombre',
        'descripcion',
        'nombre_archivo',
        'fecha_archivo',
        'nombre_real'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'cliente_id' => 'integer',
        'nombre' => 'string',
        'descripcion' => 'string',
        'nombre_archivo' => 'string',
        'nombre_real' => 'string',
        'fecha_archivo' => 'date',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'cliente_id' => 'required',
        'nombre' => 'required',
        'nombre_archivo' => 'required'
    ];

    protected $appends = ['full_url','download_url'];

    public function getFechaArchivoAttribute($value)
    {
        return ($value ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s',$value)->format('d/m/Y') : null);
    }
    public function setFechaArchivoAttribute($value)
    {
        $this->attributes['fecha_archivo'] = ($value ? \Carbon\Carbon::createFromFormat('d/m/Y',$value)->format('Y-m-d') : null);
    }      

    public function getFullUrlAttribute($value) 
    {
        return \FUHelper::fullUrl('docs',$this->nombre_archivo);
    }
    public function getDownloadUrlAttribute($value) 
    {
        return  route('download.doc_cliente',[$this->id]);
    }

}
