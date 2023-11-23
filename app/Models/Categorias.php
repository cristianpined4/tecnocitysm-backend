<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Marcas;
use App\Models\Modelos;

class Categorias extends Model
{
    use HasFactory;

    protected $table = "categorias";

    public function marca()
    {
        $modelos = $this->hasMany('App\Models\Modelos', 'id_categoria');
        $marcas = [];
        foreach ($modelos as $modelo) {
            $marcas[] = $modelo->id_marca;
        }
        return Marcas::whereIn('id', $marcas)->get();
    }

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
        return $this->morphMany('App\Models\Images', 'imageable')->where('type', 'App\Models\Categorias');
    }
}
