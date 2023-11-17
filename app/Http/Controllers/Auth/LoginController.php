<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $req)
    {
        $user = User::with('rol')->where('email', $req->email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'No existe un usuario con ese correo',
                'success' => false
            ]);
        }

        if (!Hash::check($req->password, $user->password)) {
            return response()->json([
                'message' => 'Contraseña incorrecta',
                'success' => false
            ]);
        }

        $user->token_web = $req->token_web;
        $user->update();
        Auth::login($user);
        return response()->json([
            'message' => 'Sesión iniciada correctamente',
            'success' => true,
            'token' => $user->token_web
        ]);
    }

    public function check()
    {
        if (auth()->user()) {
            return response()->json([
                'message' => 'Sesión iniciada',
                'success' => true,
                'user' => auth()->user()
            ]);
        } else {
            return response()->json([
                'message' => 'Sesión no iniciada',
                'success' => false
            ]);
        }
    }

    public function logout()
    {
        $current_User = User::find(auth()->user()->id);
        $current_User->token_web = null;
        $current_User->update();
        Auth::logout();
        return response()->json([
            'message' => 'Sesión cerrada correctamente',
            'success' => true
        ]);
    }
}