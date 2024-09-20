<?php

use App\Http\Controllers\Api\AuthenticationController;
use App\Http\Controllers\Api\TransformerController;
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
Route::group(['namespace' => 'api', 'prefix' => 'v1'], function () {
    Route::post('login', [AuthenticationController::class, 'store']);
    Route::post('logout', [AuthenticationController::class, 'destroy'])->middleware('auth:api');
    Route::post('convert', [TransformerController::class, 'pdfConvert']);
});

Route::middleware(['auth'])->get('/user', function (Request $request) {
    return $request->user();
});

