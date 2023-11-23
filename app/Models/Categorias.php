<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Marcas;


class Categorias extends Model
{
    use HasFactory;

    protected $table = "categorias";

    protected $fillable = [
        'nombre',
        'descripcion',
        'status',
        'slug',
    ];

    public function productos()
    {
        return $this->hasMany('App\Models\Productos', 'id_categoria');
    }

    public function modelos()
    {
        return $this->hasMany('App\Models\Modelos', 'id_categoria');
    }

    public function images()
    {
        return $this->hasMany('App\Models\Images', 'imageable', 'id')->where('type', 'App\Models\Categorias');
    }
}
