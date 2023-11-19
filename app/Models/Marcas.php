<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marcas extends Model
{
    use HasFactory;

    protected $table = "marcas";

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
        return $this->morphMany('App\Models\Images', 'imageable');
    }
}