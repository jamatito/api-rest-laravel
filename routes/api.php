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


Route::group(['middleware' => 'cors'], function () {
    Route::post('/user/register', 'UserController@register');
    Route::post('/user/login', 'UserController@login');
    Route::put('/user/update', 'UserController@update')->middleware(ApiAuthMiddleware::class);
    Route::post('/user/upload', 'UserController@upload')->middleware(ApiAuthMiddleware::class);
    Route::get('/user/getimage/{filename}', 'UserController@getImage');
    Route::get('/user/detail/{id}', 'UserController@detail');

    Route::apiResource('category', 'CategoryController');

    Route::apiResource('post', 'PostController');
    Route::post('/post/upload', 'PostController@upload');
    Route::get('/post/getimage/{filename}', 'PostController@getImage');

    Route::get('/post/category/{id}', 'PostController@getPostsByCategory');
    Route::get('/post/user/{id}', 'PostController@getPostsByUser');

});