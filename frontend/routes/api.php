<?php

use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\ProfileApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Usuario autenticado actual
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user()->load('roles', 'profile');
});

// Productos - publico para lectura
Route::get('/products',                 [ProductApiController::class, 'index']);
Route::get('/products/categories/list', [ProductApiController::class, 'categories']);
Route::get('/products/{product}',       [ProductApiController::class, 'show']);

// Productos - solo admin autenticado
Route::middleware(['auth:sanctum', 'role:administrador'])->group(function () {
    Route::post('/products',            [ProductApiController::class, 'store']);
    Route::put('/products/{product}',   [ProductApiController::class, 'update']);
    Route::delete('/products/{product}',[ProductApiController::class, 'destroy']);
});

// Perfil - requiere autenticacion
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile',  [ProfileApiController::class, 'show']);
    Route::put('/profile',  [ProfileApiController::class, 'update']);

    Route::get('/payments',          [PaymentApiController::class, 'index']);
    Route::post('/payments',         [PaymentApiController::class, 'store']);
    Route::get('/payments/{payment}',[PaymentApiController::class, 'show']);
});