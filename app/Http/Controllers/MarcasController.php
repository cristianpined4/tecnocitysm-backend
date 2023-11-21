<?php

    namespace App\Http\Controllers;

    use App\Models\Categorias;
    use Illuminate\Http\Request;
    use App\Models\Marcas;
    use App\Models\Images;
    use Illuminate\Support\Facades\File;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    class MarcasController extends Controller
    {

        public function __construct()
        {
            $this->middleware('auth:api')->except(['getMarcas', 'show']);
        }

        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Http\Response
         */
        public function index()
        {
            $marcas = Marcas::with('images')->get();
            return response()->json([
                'message' => "Marcas obtenidas exitosamente",
                'success' => true,
                'marcas' => $marcas,
            ], 200);
        }

        public function getMarcas()
        {
            $marcas = Marcas::with('images')->where('status', 'activo')->get();
            return response()->json([
                'message' => "Marcas obtenidas exitosamente",
                'success' => true,
                'marcas' => $marcas,
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
            $slugMarca = Str::slug($request->nombre, '-');
            if (Marcas::where('slug', 'like', "%$slugMarca%")->first()) {
                $numExistente = Marcas::where('slug', 'like', "%$slugMarca%")->count();
                $slugMarca = $slugMarca . '-' . ($numExistente + 1);
            }
            $request->merge([
                'slug' => $slugMarca,
            ]);
            $nuevo = Marcas::create($request->all());

            if ($request->hasFile('imagen')) {
                $image = $request->file('imagen');
                $extension = $image->getClientOriginalExtension();
                $imageName = Str::uuid() . '.' . $extension;

                if (! Storage::disk('images-marcas')->put($imageName, File::get($image))) {
                    return response()->json([
                        'message' => "Error al guardar la imagen",
                        'success' => false,
                    ], 500);
                }

                Images::create([
                    'url' => Storage::disk('images-marcas')->url($imageName),
                    'path' => Storage::disk('images-marcas')->path($imageName),
                    'imageable' => $nuevo->id,
                    'type' => 'App\Models\Marcas',
                ]);
            }

            return response()->json([
                'message' => "Marca creada exitosamente",
                'success' => true,
                'marca' => $nuevo,
            ], 200);
        }

        /**
         * Display the specified resource.
         *
         * @param int $id
         * @return \Illuminate\Http\Response
         */
        public function show($id)
        {

            $marca = Marcas::where('slug', $id)->with('images', 'productos', 'modelos')->first();
            return response()->json([
                'message' => "Marca obtenida exitosamente",
                'success' => true,
                'marca' => $marca,
            ], 200);
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
            $marca = Marcas::find($id);
            if (! $marca) {
                return response()->json([
                    'message' => "Marca no encontrada",
                    'success' => false,
                ], 404);
            }
            $marca->update($request->all());

            if ($request->hasFile('imagen')) {
                $image = $request->file('imagen');
                $extension = $image->getClientOriginalExtension();
                $imageName = Str::uuid() . '.' . $extension;

                $imagenActual = Images::where('imageable', $marca->id)->where('type', 'App\Models\Marcas')->first();
                if ($imagenActual) {
                    Storage::disk('images-marcas')->delete($imagenActual->path);
                    $imagenActual->delete();
                }

                if (! Storage::disk('images-marcas')->put($imageName, File::get($image))) {
                    return response()->json([
                        'message' => "Error al guardar la imagen",
                        'success' => false,
                    ], 500);
                }

                Images::create([
                    'url' => Storage::disk('images-marcas')->url($imageName),
                    'path' => Storage::disk('images-marcas')->path($imageName),
                    'imageable' => $marca->id,
                    'type' => 'App\Models\Marcas',
                ]);
            }
            return response()->json([
                'message' => "Marca actualizada exitosamente",
                'success' => true,
                'marca' => $marca,
            ], 200);
        }

        /**
         * Remove the specified resource from storage.
         *
         * @param int $id
         * @return \Illuminate\Http\Response
         */
        public function destroy($id)
        {
            $marca = Marcas::find($id);
            if (! $marca) {
                return response()->json([
                    'message' => "Marca no encontrada",
                    'success' => false,
                ], 404);
            }

            $imagenActual = Images::where('imageable', $marca->id)->where('type', 'App\Models\Marcas')->first();

            if ($imagenActual) {
                Storage::disk('images-marcas')->delete($imagenActual->path);
                $imagenActual->delete();
            }

            $marca->delete();

            return response()->json([
                'message' => "Marca eliminada exitosamente",
                'success' => true,
                'marca' => $marca,
            ], 200);
        }
    }
