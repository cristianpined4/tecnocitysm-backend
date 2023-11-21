<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /**
     * Registro de usuario
     */
    public function signUp(Request $request)
    {

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
            'rol_id' => 2,
        ]);

        $tokenResult = $user->createToken('Personal Access Token');

        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        return response()->json([
            'message' => "Usuario creado exitosamente",
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString(),
            'success' => true,
            'current_user' => $user->with('rol')->where('id', $user->id)->first(),
        ], 201);
    }

    /**
     * Inicio de sesión y creación de token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials))
            return response()->json([
                'message' => "No autorizado",
                'success' => false,
            ], 401);

        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');

        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString(),
            'success' => true,
            'current_user' => Auth::user()->with('rol')->where('id', Auth::user()->id)->first(),
        ]);
    }

    /**
     * Cierre de sesión (anular el token)
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => "Sesión cerrada exitosamente",
            'success' => true,
        ]);
    }

    /**
     * Obtener el objeto User como json
     */
    public function user(Request $request)
    {
        return response()->json($request->user()->with('rol')->first());
    }
}
