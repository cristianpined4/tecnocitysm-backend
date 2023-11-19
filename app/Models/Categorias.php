<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorias extends Model
{
    use HasFactory;

    protected $table = "categorias";

    public function marcas()
    {
        return $this->hasMany('App\Models\Marcas', 'id_categoria');
    }

    public function productos()
    {
        return $this->hasMany('App\Models\Productos', 'id_categoria');
    }

    public function modelos($id_marca)
    {
        return $this->hasMany('App\Models\Modelos', 'id_marca')->where('id_marca', $id_marca);
    }

    public function images()
    {
        return $this->morphMany('App\Models\Images', 'imageable')->where('type', 'App\Models\Categorias');
    }
}