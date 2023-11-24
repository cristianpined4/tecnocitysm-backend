<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marcas extends Model
{
    use HasFactory;

    protected $table = "marcas";

    protected $fillable = [
        'nombre',
        'descripcion',
        'status',
        'slug',
    ];

    public function productos()
    {
        return $this->hasMany('App\Models\Productos', 'id_marca');
    }

    public function modelos()
    {
        return $this->hasMany('App\Models\Modelos', 'id_marca');
    }

    public function images()
    {
        return $this->hasMany('App\Models\Images', 'imageable', 'id')->where('type', 'App\Models\Marcas');
    }
}