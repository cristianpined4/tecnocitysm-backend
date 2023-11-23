<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Productos;
use App\Models\Images;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

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
        if (Auth::user()->rol_id != 1) {
            return redirect()->route('not-authorized');
        }
        $productos = Productos::with(['marca', 'modelo', 'categoria', 'images'])->get();
        return response()->json([
            'productos' => $productos,
            'message' => 'Lista de productos obtenida correctamente',
            'success' => true,
        ], 200);
    }

    public function getProductos()
    {
        $productos = Productos::with(['marca', 'modelo', 'categoria', 'images'])->where('status', 'activo')->get();
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
        if (Auth::user()->rol_id != 1) {
            return redirect()->route('not-authorized');
        }
        $slugProducto = str_replace(' ', '-', $request->nombre);
        $slugProducto = strtolower($slugProducto);
        if (Productos::where('slug', $slugProducto)->first()) {
            $numExistente = Productos::where('slug', 'like', "%$slugProducto%")->count();
            $slugProducto = $slugProducto . '-' . ($numExistente + 1);
        }

        $request->merge([
            'slug' => $slugProducto,
        ]);

        $producto = Productos::create($request->all());

        if ($request->imagen && $request->imagen != "") {
            $images = explode(',', $request->imagen);
            foreach ($images as $imageItem) {
                $url = $imageItem;
                $extension = explode('/', explode(':', substr($url, 0, strpos($url, ';')))[1])[1];
                $replace = substr($url, 0, strpos($url, ',') + 1);
                $image = str_replace($replace, '', $url);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::uuid() . '.' . $extension;

                if (!Storage::disk('images-productos')->put($imageName, base64_decode($image))) {
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
        if (!$producto = Productos::with(['marca', 'modelo', 'categoria', 'images'])->where('slug', $slug)->first()) {
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
        if (Auth::user()->rol_id != 1) {
            return redirect()->route('not-authorized');
        }
        $productoActual = Productos::find($id);
        if (!$productoActual) {
            return response()->json([
                'message' => 'Producto no encontrado',
                'success' => false,
            ], 404);
        }

        $slugProducto = str_replace(' ', '-', $request->nombre);
        $slugProducto = strtolower($slugProducto);
        if (Productos::where('slug', $slugProducto)->first()) {
            $numExistente = Productos::where('slug', 'like', "%$slugProducto%")->count();
            $slugProducto = $slugProducto . '-' . ($numExistente + 1);
        }

        $request->merge([
            'slug' => $slugProducto,
        ]);

        $productoActual->update($request->all());

        if ($request->imagen && $request->imagen != "") {
            $images = explode(',', $request->imagen);
            foreach ($images as $imageItem) {
                $url = $imageItem;
                $extension = explode('/', explode(':', substr($url, 0, strpos($url, ';')))[1])[1];
                $replace = substr($url, 0, strpos($url, ',') + 1);
                $image = str_replace($replace, '', $url);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::uuid() . '.' . $extension;

                if (!Storage::disk('images-productos')->put($imageName, base64_decode($image))) {
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
        if (Auth::user()->rol_id != 1) {
            return redirect()->route('not-authorized');
        }
        $image = Images::find($id);
        $currentID = $image->imageable;
        if (!$image) {
            return response()->json([
                'message' => 'Imagen no encontrada',
                'success' => false,
            ], 404);
        }

        if (is_file($image->path)) {
            unlink($image->path);
            $image->delete();
        } {
            $image->delete();
        }

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
        if (Auth::user()->rol_id != 1) {
            return redirect()->route('not-authorized');
        }
        $productoActual = Productos::find($id);
        if (!$productoActual) {
            return response()->json([
                'message' => 'Producto no encontrado',
                'success' => false,
            ], 404);
        }

        $images = Images::where('imageable', $productoActual->id)->where('type', 'App\Models\Productos')->get();
        foreach ($images as $image) {
            if (is_file($image->path)) {
                unlink($$image->path);
                $image->delete();
            } {
                $image->delete();
            }
        }

        $productoActual->delete();

        return response()->json([
            'message' => 'Producto eliminado correctamente',
            'success' => true,
        ], 200);
    }
}
