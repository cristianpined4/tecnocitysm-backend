<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Productos extends Model
{
    use HasFactory;

    protected $table = "productos";

    public function categoria()
    {
        return $this->belongsTo('App\Models\Categorias', 'id_categoria');
    }

    public function marca()
    {
        return $this->belongsTo('App\Models\Marcas', 'id_marca');
    }

    public function modelo()
    {
        return $this->belongsTo('App\Models\Modelos', 'id_modelo');
    }

    public function images()
    {
        return $this->morphMany('App\Models\Images', 'imageable')->where('type', 'App\Models\Productos');
    }

    public function ventas()
    {
        return $this->belongsToMany('App\Models\Ventas', 'detalle_ventas', 'id_producto', 'id_venta');
    }
}