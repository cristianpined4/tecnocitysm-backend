<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Images;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ProductosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['show', 'getProductos']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $productos = Producto::with(['marca', 'modelo', 'categoria', 'images'])->get();
        return response()->json([
            'productos' => $productos,
            'message' => 'Lista de productos obtenida correctamente',
            'success' => true,
        ], 200);
    }

    public function getProductos()
    {
        $productos = Producto::with(['marca', 'modelo', 'categoria', 'images'])->where('status', 'activo')->get();
        return response()->json([
            'productos' => $productos,
            'message' => 'Lista de productos obtenida correctamente',
            'success' => true,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $slugProducto = str_replace(' ', '-', $request->nombre);
        $slugProducto = strtolower($slugProducto);
        if (Producto::where('slug', $slugProducto)->first()) {
            $numExistente = Producto::where('slug', 'like', "%$slugProducto%")->count();
            $slugProducto = $slugProducto . '-' . ($numExistente + 1);
        }

        $request->merge([
            'slug' => $slugProducto,
        ]);

        $producto = Producto::create($request->all());

        if ($request->hasFile('images')) {
            $images = $request->file('images');
            foreach ($images as $image) {
                $extension = $image->getClientOriginalExtension();
                $imageName = Str::uuid() . '.' . $extension;

                if (!Storage::disk('images-productos')->put($imageName, File::get($image))) {
                    return response()->json([
                        'message' => "Error al guardar la imagen",
                        'success' => false,
                    ], 500);
                }

                Images::create([
                    'url' => Storage::disk('images-productos')->url($imageName),
                    'path' => Storage::disk('images-productos')->path($imageName),
                    'imageable' => $categoria->id,
                    'type' => 'App\Models\Productos',
                ]);
            }
        }

        return response()->json([
            'producto' => $producto,
            'message' => 'Producto creado correctamente',
            'success' => true,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {

        if (!$producto = Producto::with(['marca', 'modelo', 'categoria', 'images'])->where('slug', $slug)->first()) {
            return response()->json([
                'message' => 'Producto no encontrado',
                'success' => false,
            ], 404);
        }

        return response()->json([
            'producto' => $producto,
            'message' => 'Producto obtenido correctamente',
            'success' => true,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $productoActual = Producto::find($id);
        if (!$productoActual) {
            return response()->json([
                'message' => 'Producto no encontrado',
                'success' => false,
            ], 404);
        }

        $slugProducto = str_replace(' ', '-', $request->nombre);
        $slugProducto = strtolower($slugProducto);
        if (Producto::where('slug', $slugProducto)->first()) {
            $numExistente = Producto::where('slug', 'like', "%$slugProducto%")->count();
            $slugProducto = $slugProducto . '-' . ($numExistente + 1);
        }

        $request->merge([
            'slug' => $slugProducto,
        ]);

        $productoActual->update($request->all());

        if ($request->hasFile('images')) {
            $images = $request->file('images');
            foreach ($images as $image) {
                $extension = $image->getClientOriginalExtension();
                $imageName = Str::uuid() . '.' . $extension;

                if (!Storage::disk('images-productos')->put($imageName, File::get($image))) {
                    return response()->json([
                        'message' => "Error al guardar la imagen",
                        'success' => false,
                    ], 500);
                }

                Images::create([
                    'url' => Storage::disk('images-productos')->url($imageName),
                    'path' => Storage::disk('images-productos')->path($imageName),
                    'imageable' => $productoActual->id,
                    'type' => 'App\Models\Productos',
                ]);
            }
        }

        return response()->json([
            'producto' => $productoActual,
            'message' => 'Producto actualizado correctamente',
            'success' => true,
        ], 200);
    }

    public function deleteOneImages($id)
    {
        $image = Images::find($id);
        $currentID = $image->imageable;
        if (!$image) {
            return response()->json([
                'message' => 'Imagen no encontrada',
                'success' => false,
            ], 404);
        }

        if (!Storage::disk('images-productos')->delete($image->path)) {
            return response()->json([
                'message' => "Error al eliminar la imagen",
                'success' => false,
            ], 500);
        }

        $image->delete();



        return response()->json([
            'message' => 'Imagen eliminada correctamente',
            'success' => true,
            'images' => Images::where('imageable', $currentID)->where('type', 'App\Models\Productos')->get(),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $productoActual = Producto::find($id);
        if (!$productoActual) {
            return response()->json([
                'message' => 'Producto no encontrado',
                'success' => false,
            ], 404);
        }

        $images = Images::where('imageable', $productoActual->id)->where('type', 'App\Models\Productos')->get();
        foreach ($images as $image) {
            Storage::disk('images-productos')->delete($image->path);
            $image->delete();
        }

        $productoActual->delete();

        return response()->json([
            'message' => 'Producto eliminado correctamente',
            'success' => true,
        ], 200);
    }
}