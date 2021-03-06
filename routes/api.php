<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => '/v1'], function(){
    Route::post('categories', 'App\Http\Controllers\CategoryController@store');

    Route::post('todo-list', 'App\Http\Controllers\TODOController@store');

    Route::post('track-item/{item_id}', 'App\Http\Controllers\TODOController@trackItem');
    Route::post('stop-item/{item_id}', 'App\Http\Controllers\TODOController@stopItem');

    Route::get('report1', 'App\Http\Controllers\ReportController@getReport1Result');
    Route::get('report2', 'App\Http\Controllers\ReportController@getReport2Result');

});

