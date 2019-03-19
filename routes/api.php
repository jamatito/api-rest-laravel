<?php

use App\Http\Middleware\ApiAuthMiddleware;
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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

//Route::apiResource('users','UserController');

Route::post('/users/register', 'UserController@register');
Route::post('/users/login', 'UserController@login');
Route::put('/users/update', 'UserController@update')->middleware(ApiAuthMiddleware::class);
Route::post('/users/upload', 'UserController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('/users/getimage/{filename}', 'UserController@getImage');
Route::get('/users/detail/{id}', 'UserController@detail');

Route::apiResource('category','CategoryController');