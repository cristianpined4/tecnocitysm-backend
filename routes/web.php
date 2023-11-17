<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\Roles;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get("init", function () {

    $rol = roles::find(1);
    if ($rol) {
        return response()->json([
            "message" => "Base de datos ya inicializada",
            "success" => false
        ]);
    }

    $rol = new Roles();
    $rol->name = "Administador";
    $rol->save();
    $rol = new Roles();
    $rol->name = "Usuario";
    $rol->save();
    $user = new User();
    $user->name = "admin";
    $user->username = "admin";
    $user->email = "admin@admin.com";
    $user->password = Hash::make("admin");
    $user->rol_id = 1;
    $user->save();

    return response()->json([
        "message" => "Base de datos inicializada correctamente",
        "success" => true
    ]);
});

Auth::routes();