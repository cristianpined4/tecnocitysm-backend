<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Productos extends Model
{
    use HasFactory;

    protected $table = "productos";

    protected $fillable = [
        'nombre',
        'descripcion',
        'status',
        'id_categoria',
        'id_marca',
        'id_modelo',
        'precio',
        'stock',
        'slug',
    ];

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
        return $this->hasMany('App\Models\Images', 'imageable', 'id')->where('type', 'App\Models\Productos');
    }

    public function ventas()
    {
        return $this->belongsToMany('App\Models\Ventas', 'detalle_ventas', 'id_producto', 'id_venta');
    }
}
