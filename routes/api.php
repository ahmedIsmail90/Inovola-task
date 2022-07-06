<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;



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


    Route::middleware(['auth:sanctum','is.merchant'])->group( function () {
    Route::resource('Store', StoreController::class);
    Route::POST('/store/{store}/products/', [ProductController::class, 'store']);
    Route::PUT('/products/{product}', [ProductController::class, 'update']);
});

Route::middleware(['auth:sanctum','is.customer'])->group( function () {

    Route::POST('store/{store}/cart/items/', [CartController::class, 'addItems']);
    Route::get('/cart/{cart}', [CartController::class, 'show']);
    Route::put('/cart/{cart}/item/{item}', [CartController::class, 'removeItem']);

});


