<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Modelos;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;


class ModelosController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api')->except(['getModelos', 'show']);
    }

    public function getModelos()
    {
        $modelos = Modelos::where('status', 'activo')->get();
        return response()->json([
            'message' => "Modelos obtenidos exitosamente",
            'success' => true,
            'modelos' => $modelos,
        ], 200);
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
        $modelos = Modelos::with('marca', 'categoria')->get();
        return response()->json([
            'message' => "Modelos obtenidos exitosamente",
            'success' => true,
            'modelos' => $modelos,
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Auth::user()->rol_id != 1) {
            return redirect()->route('not-authorized');
        }
        $slugModelo = Str::slug($request->nombre, '-');
        $slugModelo = strtolower($slugModelo);
        if (Modelos::where('slug', 'like', "%$slugModelo%")->first()) {
            $numExistente = Modelos::where('slug', 'like', "%$slugModelo%")->count();
            $slugModelo = $slugModelo . '-' . ($numExistente + 1);
        }
        $request->merge([
            'slug' => $slugModelo,
        ]);
        $modelo = Modelos::create($request->all());

        return response()->json([
            'message' => "Modelo creado exitosamente",
            'success' => true,
            'modelo' => $modelo,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $modelo = Modelos::where('id', $id)->first();
        if ($modelo) {
            return response()->json([
                'message' => "Modelo obtenido exitosamente",
                'success' => true,
                'modelo' => $modelo,
            ], 200);
        } else {
            return response()->json([
                'message' => "Modelo no encontrado",
                'success' => false,
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->rol_id != 1) {
            return redirect()->route('not-authorized');
        }
        $modelo = Modelos::where('id', $id)->first();
        if ($modelo) {
            $modelo->update($request->all());
            return response()->json([
                'message' => "Modelo actualizado exitosamente",
                'success' => true,
                'modelo' => $modelo,
            ], 200);
        } else {
            return response()->json([
                'message' => "Modelo no encontrado",
                'success' => false,
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Auth::user()->rol_id != 1) {
            return redirect()->route('not-authorized');
        }
        $modelo = Modelos::where('id', $id)->first();
        if ($modelo) {
            $modelo->delete();
            return response()->json([
                'message' => "Modelo eliminado exitosamente",
                'success' => true,
            ], 200);
        } else {
            return response()->json([
                'message' => "Modelo no encontrado",
                'success' => false,
            ], 404);
        }
    }
}
