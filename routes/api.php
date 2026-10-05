<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\Api\OrderController;

Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{order}', [OrderController::class, 'show']);
Route::post('/orders/{order}/items', [OrderController::class, 'addItem']);
Route::get('/orders/{order}/payments', [OrderController::class, 'payments']);
Route::post('/orders/{order}/payments', [OrderController::class, 'addPayment']);
