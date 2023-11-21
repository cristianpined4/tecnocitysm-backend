<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signUp');

    Route::group([
        'middleware' => 'auth:api'
    ], function () {
        Route::get('logout', 'AuthController@logout');
        Route::get('current-user', 'AuthController@user');
    });
});

// aqui van las rutas que no ocupan token
Route::get('not-authorized', function () {
    return response()->json([
        'message' => "No autorizado",
        'success' => false,
    ], 401);
})->name('not-authorized');

Route::resource('categorias', 'CategoriasController');
