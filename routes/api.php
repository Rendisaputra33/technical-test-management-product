<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\MutationController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/refresh', [AuthController::class, 'refreshToken']);
    Route::post('auth/validate', [AuthController::class, 'validateToken']);

    Route::middleware(['jwt.auth'])->group(function () {
        Route::get('auth/profile', [AuthController::class, 'profile']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::apiResource('categories', CategoryController::class);

        Route::apiResource('products', ProductController::class);
        Route::get('products/search', [ProductController::class, 'search']);
        Route::get('categories/{category}/products', [ProductController::class, 'getByCategory']);

        Route::apiResource('locations', LocationController::class);

        Route::apiResource('stock', StockController::class);
        Route::get('products/{product}/stock', [StockController::class, 'getProductStock']);
        Route::get('locations/{location}/stock', [StockController::class, 'getLocationStock']);
        Route::post('stock/get', [StockController::class, 'getStock']);

        Route::apiResource('mutations', MutationController::class);
        Route::get('mutations/user/histories', [MutationController::class, 'getByUser']);
        Route::get('mutations/product/{product}/histories', [MutationController::class, 'getByProduct']);
        Route::get('reports/stock', [MutationController::class, 'getStockReport']);
    });
});
