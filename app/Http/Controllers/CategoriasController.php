<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categorias;
use App\Models\Images;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class CategoriasController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api')->except(['getCategorias', 'show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categorias = Categorias::with('images', 'marcas')->get();
        return response()->json([
            'message' => "Categorias obtenidas exitosamente",
            'success' => true,
            'categorias' => $categorias,
        ], 200);
    }


    public function getCategorias()
    {
        $categorias = Categorias::with('images', 'marcas')->where('status', 'activo')->get();
        return response()->json([
            'message' => "Categorias obtenidas exitosamente",
            'success' => true,
            'categorias' => $categorias,
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
        $slugCategoria = Str::slug($request->nombre, '-');
        if (Categorias::where('slug', 'like', "%$slugCategoria%")->first()) {
            $numExistente = Categorias::where('slug', 'like', "%$slugCategoria%")->count();
            $slugCategoria = $slugCategoria . '-' . ($numExistente + 1);
        }
        $request->merge([
            'slug' => $slugCategoria,
        ]);
        $categoria = Categorias::create($request->all());

        if ($request->hasFile('imagen')) {
            $image = $request->file('imagen');
            $extension = $image->getClientOriginalExtension();
            $imageName = Str::uuid() . '.' . $extension;

            if (!Storage::disk('images-marcas')->put($imageName, File::get($image))) {
                return response()->json([
                    'message' => "Error al guardar la imagen",
                    'success' => false,
                ], 500);
            }

            Images::create([
                'url' => Storage::disk('images-categorias')->url($imageName),
                'path' => Storage::disk('images-categorias')->path($imageName),
                'imageable' => $categoria->id,
                'type' => 'App\Models\Categorias',
            ]);
        }

        return response()->json([
            'message' => "Categoria creada exitosamente",
            'success' => true,
            'categoria' => $categoria,
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $categoria = Categorias::where('slug', $slug)->with('images', 'marcas')->first();
        if (!$categoria) {
            return response()->json([
                'message' => "Categoria no encontrada",
                'success' => false,
            ], 404);
        }
        return response()->json([
            'message' => "Categoria obtenida exitosamente",
            'success' => true,
            'categoria' => $categoria,
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
        $categoria = Categorias::find($id);
        $categoria->update($request->all());

        if ($request->hasFile('imagen')) {
            $image = $request->file('imagen');
            $extension = $image->getClientOriginalExtension();
            $imageName = Str::uuid() . '.' . $extension;

            $currentImage = Images::where('imageable', $categoria->id)->where('type', 'App\Models\Categorias')->first();
            if ($currentImage) {
                Storage::disk('images-categorias')->delete($currentImage->path);
                $currentImage->delete();
            }

            if (!Storage::disk('images-categorias')->put($imageName, File::get($image))) {
                return response()->json([
                    'message' => "Error al guardar la imagen",
                    'success' => false,
                ], 500);
            }

            Images::create([
                'url' => Storage::disk('images-categorias')->url($imageName),
                'path' => Storage::disk('images-categorias')->path($imageName),
                'imageable' => $categoria->id,
                'type' => 'App\Models\Categorias',
            ]);
        }

        return response()->json([
            'message' => "Categoria actualizada exitosamente",
            'success' => true,
            'categoria' => $categoria,
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
        $categoria  = Categorias::find($id);
        $image = Images::where('imageable', $categoria->id)->where('type', 'App\Models\Categorias')->first();
        if ($image) {
            Storage::disk('images-categorias')->delete($image->path);
            $image->delete();
        }
        $categoria->delete();
        return response()->json([
            'message' => "Categoria eliminada exitosamente",
            'success' => true,
        ], 200);
    }
}