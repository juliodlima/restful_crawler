<?php

use Illuminate\Http\Request;

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

// teste da Api
Route::middleware('api')->get('/', function () {
    return response()->json(['message' => 'API Card', 'status' => 'Connected']);
});

// **************** Crawler *********************************************
Route::middleware('api')->post("/busca","CrawlerController@busca");
Route::middleware('api')->post("/detalhes","CrawlerController@detalhes");
