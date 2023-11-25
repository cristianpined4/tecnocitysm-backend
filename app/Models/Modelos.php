<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modelos extends Model
{
    use HasFactory;

    protected $table = "modelos";

    protected $fillable = [
        'nombre',
        'descripcion',
        'status',
        'id_marca',
        'id_categoria',
        'slug'
    ];

    public function productos()
    {
        return $this->hasMany('App\Models\Productos', 'id_modelo');
    }

    public function marca()
    {
        return $this->belongsTo('App\Models\Marcas', 'id_marca');
    }

    public function categoria()
    {
        return $this->belongsTo('App\Models\Categorias', 'id_categoria');
    }

    public function images()
    {
        return $this->morphMany('App\Models\Images', 'imageable')->where('type', 'App\Models\Modelos');
    }
}
