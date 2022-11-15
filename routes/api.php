<?php

use App\Http\Controllers\OffsiteController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 获取影片信息
Route::get('/get/movie/info', [OffsiteController::class, 'getMovieInfo']);
// 存储影片信息到数据库
Route::post('save/info', [OffsiteController::class, 'saveInfoToDatabase']);

Route::get('index', [OffsiteController::class, 'index']);
