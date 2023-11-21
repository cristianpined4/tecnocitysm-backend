<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_marca');
            $table->unsignedBigInteger('id_modelo');
            $table->unsignedBigInteger('id_categoria');
            $table->string('nombre');
            $table->string('descripcion');
            $table->string('status')->default('activo');
            $table->string('slug')->unique();
            $table->foreign('id_marca')->references('id')->on('marcas')->onDelete('cascade');
            $table->foreign('id_modelo')->references('id')->on('modelos')->onDelete('cascade');
            $table->foreign('id_categoria')->references('id')->on('categorias')->onDelete('cascade');
            $table->integer('stock');
            $table->double('precio_compra');
            $table->double('precio_venta');
            $table->string('codigo_barras');
            $table->string('codigo_producto');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('productos');
    }
}