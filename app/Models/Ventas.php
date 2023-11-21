<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ventas extends Model
{
    use HasFactory;
    protected $table = "ventas";

    public function detalleVentas()
    {
        return $this->hasMany('App\Models\Ventas', 'id_venta');
    }

    public function cliente()
    {
        return $this->belongsTo('App\Models\User', 'id_cliente');
    }

    public function productos()
    {
        return $this->belongsToMany('App\Models\Productos', 'detalle_ventas', 'id_venta', 'id_producto');
    }

    public function completarVenta()
    {
        $this->status = 'completado';
        $this->save();
    }
}