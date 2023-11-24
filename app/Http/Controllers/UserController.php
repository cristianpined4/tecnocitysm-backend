<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Images;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
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
        $users = User::with('images', 'rol')->get();
        return response()->json([
            'message' => "Usuarios obtenidos exitosamente",
            'success' => true,
            'users' => $users,
            "current_user" => Auth::user(),
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

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $vericarEmail = User::where('email', $request->email)->first();
        if ($vericarEmail) {
            return response()->json([
                'message' => "El correo ya existe",
                'success' => false,
            ], 401);
        }

        $username = explode('@', $request->email)[0];
        if (User::where('username', 'like', "%$username%")->first()) {
            $numExistente = User::where('username', 'like', "%$username%")->count();
            $username = $username . ($numExistente + 1);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'username' => $username,
            'rol_id' => $request->rol_id,
        ]);

        if ($request->imagen && $request->imagen != "") {
            $url = $request->imagen;
            $extension = explode('/', explode(':', substr($url, 0, strpos($url, ';')))[1])[1];
            $replace = substr($url, 0, strpos($url, ',') + 1);
            $image = str_replace($replace, '', $url);
            $image = str_replace(' ', '+', $image);
            $imageName = Str::uuid() . '.' . $extension;

            if (!Storage::disk('images-users')->put($imageName, base64_decode($image))) {
                return response()->json([
                    'message' => "Error al guardar la imagen",
                    'success' => false,
                ], 500);
            }

            Images::create([
                'url' => Storage::disk('images-users')->url($imageName),
                'path' => Storage::disk('images-users')->path($imageName),
                'imageable' => $user->id,
                'type' => 'App\Models\User',
            ]);
        }

        return response()->json([
            'message' => "Usuario creado exitosamente",
            'success' => true,
            'user' => $user,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (Auth::user()->rol_id != 1) {
            return redirect()->route('not-authorized');
        }

        $user = User::with('images', 'rol')->find($id);
        if (!$user) {
            return response()->json([
                'message' => "Usuario no encontrado",
                'success' => false,
            ], 404);
        }

        return response()->json([
            'message' => "Usuario obtenido exitosamente",
            'success' => true,
            'user' => $user,
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

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => "Usuario no encontrado",
                'success' => false,
            ], 404);
        }

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email',
        ]);

        $vericarEmail = User::where('email', $request->email)->where('id', '!=', $id)->first();
        if ($vericarEmail) {
            return response()->json([
                'message' => "El correo ya existe",
                'success' => false,
            ], 401);
        }

        $username = explode('@', $request->email)[0];
        if (User::where('username', 'like', "%$username%")->where('id', '!=', $id)->first()) {
            $numExistente = User::where('username', 'like', "%$username%")->where('id', '!=', $id)->count();
            $username = $username . ($numExistente + 1);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $username,
            'rol_id' => $request->rol_id,
            'status' => $request->status,
        ]);

        if ($request->imagen && $request->imagen != "") {
            $url = $request->imagen;
            $extension = explode('/', explode(':', substr($url, 0, strpos($url, ';')))[1])[1];
            $replace = substr($url, 0, strpos($url, ',') + 1);
            $image = str_replace($replace, '', $url);
            $image = str_replace(' ', '+', $image);
            $imageName = Str::uuid() . '.' . $extension;

            $currentImage = Images::where('imageable', $user->id)->where('type', 'App\Models\User')->first();
            if ($currentImage) {
                if (is_file($currentImage->path)) {
                    unlink($currentImage->path);
                }
                $currentImage->delete();
            }

            if (!Storage::disk('images-users')->put($imageName, base64_decode($image))) {
                return response()->json([
                    'message' => "Error al guardar la imagen",
                    'success' => false,
                ], 500);
            }

            Images::create([
                'url' => Storage::disk('images-users')->url($imageName),
                'path' => Storage::disk('images-users')->path($imageName),
                'imageable' => $user->id,
                'type' => 'App\Models\User',
            ]);
        }

        return response()->json([
            'message' => "Usuario actualizado exitosamente",
            'success' => true,
            'user' => $user,
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

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => "Usuario no encontrado",
                'success' => false,
            ], 404);
        }

        $currentImage = Images::where('imageable', $user->id)->where('type', 'App\Models\User')->first();
        if ($currentImage) {
            if (is_file($currentImage->path)) {
                unlink($currentImage->path);
            }
            $currentImage->delete();
        }

        $user->delete();

        return response()->json([
            'message' => "Usuario eliminado exitosamente",
            'success' => true,
        ], 200);
    }
}
