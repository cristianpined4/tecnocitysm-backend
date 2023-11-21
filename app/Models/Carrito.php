<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;

    protected $table = "carritos";

    public function productos()
    {
        return $this->belongsTo('App\Models\Productos', 'id_producto');
    }

    public function users()
    {
        return $this->belongsTo('App\Models\User', 'id_cliente');
    }
}
